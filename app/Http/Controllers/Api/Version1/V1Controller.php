<?php

namespace App\Http\Controllers\Api\Version1;

use App\HandleException\BadRequestException;
use App\HandleException\NotFoundException;
use App\HandleException\QueryException;
use App\Http\Controllers\Controller;
use App\Services\V1Service;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class V1Controller extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json((new V1Service())->getAllEntity())->setStatusCode(Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json((new V1Service())->storeEntity($request))->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @throws NotFoundException
     */
    public function show(int $id): JsonResponse
    {
        return response()->json((new V1Service())->getDetailEntity($id))->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @throws BadRequestException
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        return response()->json((new V1Service())->updateEntity($request, $id))->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @throws QueryException
     */
    public function delete(int|string $id): JsonResponse
    {
        return response()->json((new V1Service())->deleteEntity($id))->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    public function deleteByIds(Request $request): JsonResponse
    {
        return response()->json((new V1Service())->deleteByIds($request));
    }
}
