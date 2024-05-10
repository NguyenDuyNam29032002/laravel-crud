<?php

namespace App\HandleException;

use Symfony\Component\HttpFoundation\Response;

class SystemException extends BaseException
{
    public int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
}
