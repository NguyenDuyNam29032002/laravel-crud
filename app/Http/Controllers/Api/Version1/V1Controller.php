<?php

namespace App\Http\Controllers\Api\Version1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEntityRequest;
use App\Service\V1Service;
use Couchbase\QueryException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class V1Controller extends Controller implements ShouldQueue
{
    public V1Service $v1Service;
    public function __construct(V1Service $v1Service)
    {
        $this->v1Service = $v1Service;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->v1Service->getAllEntity())->setStatusCode(Response::HTTP_OK);
    }
    public function store(Request $request): JsonResponse
    {
        return response()->json($this->v1Service->storeEntity($request))->setStatusCode(Response::HTTP_CREATED);
    }
    public function show(int $id): JsonResponse
    {
        return response()->json($this->v1Service->getDetailEntity($id))->setStatusCode(Response::HTTP_OK);
    }
    public function update(Request $request, int|string $id): JsonResponse
    {
        return response()->json($this->v1Service->updateEntity($request, $id))->setStatusCode(Response::HTTP_OK);
    }
    public function delete(int|string $id): JsonResponse
    {
        return response()->json($this->v1Service->deleteEntity($id))->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    public function deleteByIds(Request $request)
    {
        return response()->json($this->v1Service->deleteByIds($request));
    }
}
