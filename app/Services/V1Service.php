<?php

namespace App\Services;

use App\Contract\BaseContract;
use App\Enums\TypeEnums;
use App\HandleException\BadRequestException;
use App\HandleException\NotFoundException;
use App\Models\V1;
use App\Traits\HasRequest;
use App\Traits\Validatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class V1Service implements ShouldQueue, BaseContract
{
    use HasRequest, Validatable;

    public function getAllEntity($paginated = true): \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
    {
        $limit     = request('limit');
        $paginated = request()->boolean('paginate', $paginated);
        $names     = request('name');

        V1::query()->when($names, function (Builder $builder) use ($names) {
            $builder->where('name', 'like', '%' . $names . '%');
        });
        $entities = $paginated ? V1::query()->paginate($limit) : V1::query()->get();
        Log::info('Storing cache...');
        Cache::put('index: ', $entities->toArray(), 180);
        Log::info('cache successfully', $entities->toArray());

        return $entities;
    }

    public function storeEntity(object $request): Model
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|unique:v1_s,name|min:6',
            'description' => 'max:100',
            'type'        => 'in:' . implode(',', TypeEnums::toArray())
        ]);
        $this->mergeRequestParams($request, ['type' => TypeEnums::CREATE->value]);
        if ($validator->fails()) {
            throw new BadRequestException("request is invalid");
        }
        Log::info('Storing cache...');
        Cache::put('store: ', $request->all(), 180);
        Log::info('cache successfully', $request->all());
        $model = new V1();

        if (
            !str_contains(V1::query()->getConnection()->getDriverName(), 'mysql')
            || Schema::connection($model->getConnectionName())->hasColumn($model->getTable(), 'uuid')
        ) {
            $this->mergeRequestParams($request, ['uuid' => Uuid::uuid4()]);
        }

        Log::info("create $request->all() successfully");

        return V1::query()->create($request->all());
    }

    /**
     * @throws NotFoundException
     */
    public function getDetailEntity(int|string $id): Model
    {
        try {
            $entity = V1::query()->findOrFail($id);
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
     * @return Model
     * @throws BadRequestException
     * @throws \App\HandleException\QueryException
     */
    public function updateEntity(int|string $id, object $request): Model
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'        => 'sometimes|required',
                'description' => 'max:100'
            ]);

            $this->mergeRequestParams($request, ['type' => TypeEnums::UPDATE->value]);
            if ($validator->fails()) {
                throw new BadRequestException("request is invalid");
            }

            DB::beginTransaction();

            $entity = V1::query()->findOrFail($id);
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
            throw new \App\HandleException\QueryException(message: 'query failed', previous: $queryException);
        }
    }

    /**
     * @throws \App\HandleException\QueryException
     */
    public function deleteEntity(int|string $id): bool
    {
        try {
            $entity = V1::query()->findOrFail($id);
            DB::beginTransaction();
            $entity->delete();
            DB::commit();

            return true;
        } catch (QueryException $queryException) {
            DB::rollBack();
            throw new \App\HandleException\QueryException(message: 'entity not found', previous: $queryException);
        }
    }

    public function deleteByIds(object $request): bool
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'exists:v1_s,id,deleted_at,NULL'
        ]);
        if ($validator->fails()) {
            throw new NotFoundException("Id is invalid");
        }

        V1::query()->whereIn('id', $request->ids)->delete();

        return true;
    }
}
