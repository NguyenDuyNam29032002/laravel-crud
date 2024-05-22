<?php
/**
 * @author Me
 * @Date   May 22, 2024
 */

namespace App\Contract;

use App\HandleException\BadRequestException;
use App\HandleException\NotFoundException;
use App\HandleException\QueryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BaseContract
{
    /**
     * @description Get a list off all item
     *
     * @param bool $paginated
     *
     * @return LengthAwarePaginator|Collection
     * @throws QueryException
     */
    public function getAllEntity(bool $paginated = true): LengthAwarePaginator|Collection;

    /**
     * @description Store new entity
     *
     * @param object $request
     *
     * @return Model
     * @throws NotFoundException
     * @throws QueryException
     * @throws BadRequestException
     */
    public function storeEntity(object $request): Model;

    /**
     * @description Get entity via id
     *
     * @param int|string $id
     *
     * @return Model
     * @throws NotFoundException
     * @throws QueryException
     */
    public function getDetailEntity(int|string $id): Model;

    /**
     * @description Update an entity via id
     *
     * @param int|string $id
     * @param object     $request
     *
     * @return Model
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws QueryException
     */
    public function updateEntity(int|string $id, object $request): Model;

    /**
     * @description Delete an entity via ID
     *
     * @param int|string $id
     *
     * @return bool
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws QueryException
     */
    public function deleteEntity(int|string $id): bool;

    /**
     * @description Delete multiple entity via Ids from request
     *
     * @param object $request
     *
     * @return bool
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws QueryException
     */
    public function deleteByIds(object $request): bool;
}
