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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class V1Service implements ShouldQueue
{
    private Model|V1 $model;
    private ?string  $alias;

    public function __construct(?Model $model = null, ?string $alias = null)
    {
        $this->model = $model ?: new V1();
        $this->alias = $alias;
    }

    public function getAllEntity(): array|Collection
    {
        return $this->model::query()->get();
    }

    public function storeEntity(Request $request): Model|Builder|MessageBag
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'        => 'required|unique:v1_s,name|min:6|regex:/^[a-zA-Z0-9]+$/u',
                'description' => 'max:100'
            ]);

            if ($validator->fails()) {
               return $validator->messages();
            }
            return $this->model::query()->create($request->all());
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw new BadRequestHttpException('Model not found', $modelNotFoundException);
        }
    }

    public function getDetailEntity(int|string $id): Model|Collection|Builder|array|null
    {
        try {
            return $this->model::query()->findOrFail($id);
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw new BadRequestHttpException('Model not found', $modelNotFoundException);
        }
    }

    public function updateEntity(Request $request, int|string $id): Model|Collection|Builder|array|null
    {
        try {
            $entity = $this->model::query()->findOrFail($id);
            $obj    = $request->all();
            DB::beginTransaction();
            $entity->update($obj);
            $entity->save();
            DB::commit();

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