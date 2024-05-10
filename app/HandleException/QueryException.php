<?php

namespace App\HandleException;

use Symfony\Component\HttpFoundation\Response;

class QueryException extends BaseException
{
    public int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
}
