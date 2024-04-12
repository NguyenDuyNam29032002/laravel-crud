<?php

namespace App\Service;

use App\Enums\TypeEnums;
use App\Models\V1;
use App\Traits\HasRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class V1Service implements ShouldQueue
{
    use HasRequest;

    private Model|V1 $model;
    private ?string $alias;
    protected string $table;
    private string $driver;

    protected Builder $builder;

    public function __construct(?Model $model = null, ?string $alias = null)
    {
        $this->model = $model ?: new V1();
        $this->alias = $alias;
        $this->driver = $this->model->getConnection()->getDriverName();
        $this->table = $this->model->getTable();
    }

    public function getAllEntity()
    {
        $limit = request('limit');
        $entities = $this->model::query()->paginate($limit);
        if ($this instanceof ShouldQueue) {
            Cache::put('index: ', $entities, 180);
        }

        return $entities;
    }

    public function storeEntity(object $request): Model|Builder|MessageBag
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:v1_s,name|min:6',
            'description' => 'max:100',
            'type' => 'in:' . implode(',', TypeEnums::toArray())
        ]);
        $this->mergeRequestParams($request, ['type' => TypeEnums::CREATE->value]);
        if ($validator->fails()) {
            return $validator->messages();
        }

        if ($this instanceof ShouldQueue) {
            Cache::put('store: ', $request->all(), 180);
        }

        if (
            !str_contains($this->driver, 'mysql')
            || Schema::connection($this->model->getConnectionName())->hasColumn($this->table, 'uuid')
        ) {
            $request->merge(['uuid' => Uuid::uuid4()]);
        }

        return $this->model::query()->create($request->all());
    }

    public function getDetailEntity(int|string $id): Model|Collection|Builder|array|null
    {
        try {
            $entity = $this->model::query()->findOrFail($id);
            if ($this instanceof ShouldQueue) {
                Cache::put('show: ', $entity, 180);
            }

            return $entity;
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw new BadRequestHttpException('Model not found', $modelNotFoundException);
        }
    }

    public function updateEntity(object $request, int|string $id): Model|Collection|Builder|array|MessageBag|null
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required',
                'description' => 'max:100'
            ]);

            $this->mergeRequestParams($request, ['type' => TypeEnums::UPDATE->value]);
            if ($validator->fails()) {
                return $validator->errors()->toArray();
            }

            DB::beginTransaction();

            $entity = $this->model::query()->findOrFail($id);
            $this->removeRequestParams($request, ['uuid']);

            $entity->update($request->all());

            DB::commit();

            if ($this instanceof ShouldQueue) {
                Cache::put('update', $request->all(), 180);
            }

            return $entity;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw new BadRequestHttpException('entity not found', $exception);
        } catch (QueryException $queryException) {
            throw new BadRequestHttpException('query failed', $queryException);
        }
    }

    public function deleteEntity(int|string $id): void
    {
        try {
            $entity = $this->model::query()->findOrFail($id);
            DB::beginTransaction();
            $entity->delete();
            DB::commit();
        } catch (ModelNotFoundException $notFoundException) {
            DB::rollBack();
            throw new BadRequestHttpException('entity not found', $notFoundException);
        }
    }
}
