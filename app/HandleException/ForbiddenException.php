<?php

namespace App\HandleException;

use Symfony\Component\HttpFoundation\Response;

class ForbiddenException extends BaseException
{
    public int $statusCode = Response::HTTP_FORBIDDEN;
}
