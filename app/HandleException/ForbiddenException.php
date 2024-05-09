<?php

namespace App\Exeptions;

use Symfony\Component\HttpFoundation\Response;

class ForbiddenException extends BaseException
{
    public int $statusCode = Response::HTTP_FORBIDDEN;
}
