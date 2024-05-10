<?php

namespace App\Services;

use App\Enums\TypeEnums;
use App\HandleException\BadRequestException;
use App\HandleException\NotFoundException;
use App\Models\V1;
use App\Traits\HasRequest;
use App\Traits\Validatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class V1Service implements ShouldQueue
{
    use HasRequest, Validatable;

    private Model|V1 $model;
    private ?string  $alias;
    protected string $table;
    private string   $driver;

    protected Builder $builder;

    public function __construct(?Model $model = null, ?string $alias = null)
    {
        $this->model   = $model ?: new V1();
        $this->alias   = $alias;
        $this->driver  = $this->model->getConnection()->getDriverName();
        $this->table   = $this->model->getTable();
        $this->builder = $this->model->newQuery();
    }

    public function getAllEntity($paginated = true): Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator|array
    {
        $limit     = request('limit');
        $paginated = request()->boolean('paginate', $paginated);
        $names     = request('name');

        $this->builder->when($names, function (Builder $builder) use ($names) {
            $builder->where('name', 'like', '%' . $names . '%');
        });
        $entities = $paginated ? $this->builder->paginate($limit) : $this->builder->get();
        Log::info('Storing cache...');
        Cache::put('index: ', $entities->toArray(), 180);
        Log::info('cache successfully', $entities->toArray());

        return $entities;
    }

    public function storeEntity(object $request): Model|Builder|MessageBag
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|unique:v1_s,name|min:6',
            'description' => 'max:100',
            'type'        => 'in:' . implode(',', TypeEnums::toArray())
        ]);
        $this->mergeRequestParams($request, ['type' => TypeEnums::CREATE->value]);
        if ($validator->fails()) {
            return $validator->messages();
        }
        Log::info('Storing cache...');
        Cache::put('store: ', $request->all(), 180);
        Log::info('cache successfully', $request->all());

        if (
            !str_contains($this->driver, 'mysql')
            || Schema::connection($this->model->getConnectionName())->hasColumn($this->table, 'uuid')
        ) {
            $this->mergeRequestParams($request, ['uuid' => Uuid::uuid4()]);
        }

        Log::info("create $request->all() successfully");

        return $this->model::query()->create($request->all());
    }

    /**
     * @throws NotFoundException
     */
    public function getDetailEntity(int|string $id): Model|Collection|Builder|array|null
    {
        try {
            $entity = $this->model::query()->findOrFail($id);
            Log::info('Storing cache...');
            Cache::put('show: ', $entity, 180);
            Log::info("cache $entity successfully");
            Log::info("get $entity detail successfully");

            return $entity;
        } catch (QueryException $queryException) {
            Log::error("get $id detail failed, retry");
            throw new NotFoundException(message: "Fails", previous: $queryException);
        }
    }

    /**
     * @param object     $request
     * @param int|string $id
     *
     * @return Model|Collection|Builder|array|MessageBag|null
     * @throws BadRequestException
     */
    public function updateEntity(object $request, int|string $id): Model|Collection|Builder|array|MessageBag|null
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'        => 'sometimes|required',
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
            Log::info("Storing cache ... ");
            Cache::put('update', $request->all(), 180);
            Log::info("Cached  successfully", $request->all());

            return $entity;
        } catch (BadRequestException) {
            DB::rollBack();
            throw new BadRequestException('entity not found');
        } catch (QueryException $queryException) {
            throw new BadRequestHttpException('query failed', $queryException);
        }
    }

    /**
     * @throws \App\HandleException\QueryException
     */
    public function deleteEntity(int|string $id): void
    {
        try {
            $entity = $this->model::query()->findOrFail($id);
            DB::beginTransaction();
            $entity->delete();
            DB::commit();
        } catch (QueryException $queryException) {
            DB::rollBack();
            throw new \App\HandleException\QueryException(message: 'entity not found', previous: $queryException);
        }
    }

    public function deleteByIds(object $request): bool|MessageBag
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'exists:v1_s,id,deleted_at,NULL'
        ]);
        if ($validator->fails()) {
            return $validator->messages();
        }

        $this->model::query()->whereIn('id', $request->ids)->delete();

        return true;
    }
}
