<?php

namespace App\Service;

use App\Models\V1;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class V1Service implements ShouldQueue
{
    private Model|V1 $model;
    private ?string  $alias;
    protected string $table;
    private string   $driver;

    public function __construct(?Model $model = null, ?string $alias = null)
    {
        $this->model  = $model ?: new V1();
        $this->alias  = $alias;
        $this->driver = $this->model->getConnection()->getDriverName();
        $this->table  = $this->model->getTable();
    }

    public function getAllEntity(): array|Collection
    {
        $entities = $this->model::query()->get();
        if ($this instanceof ShouldQueue) {
            Cache::put('index: ', $entities, 180);
        }

        return $entities;
    }

    public function storeEntity(Request $request): Model|Builder|MessageBag
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'        => 'required|unique:v1_s,name|min:6',
                'description' => 'max:100'
            ]);

            if ($validator->fails()) {
                return $validator->messages();
            }

            if ($this instanceof ShouldQueue) {
                Cache::put('store: ', $request->all(), 180);
            }
            // if using mysql, with table has uuid column, then Set uuid
            if (!str_contains($this->driver, 'mysql')
                || Schema::connection($this->model->getConnectionName())->hasColumn($this->table, 'uuid')) {
                $request->merge(['uuid' => Uuid::uuid4()]);
            }

            return $this->model::query()->create($request->all());
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw new BadRequestHttpException('Model not found', $modelNotFoundException);
        }
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

    public function updateEntity(Request $request, int|string $id): Model|Collection|Builder|array|MessageBag|null
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'        => 'sometimes|required',
                'description' => 'max: 100'
            ]);
            if ($validator->fails()) {
                return $validator->messages();
            }
            $entity = $this->model::query()->findOrFail($id);
            $obj    = $request->all();
            DB::beginTransaction();
            $entity->update($obj);
            DB::commit();
            if ($this instanceof ShouldQueue) {
                Cache::put('update', $obj, 180);
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