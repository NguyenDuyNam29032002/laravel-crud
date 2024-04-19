<?php

namespace App\Exeptions;

use Symfony\Component\HttpFoundation\Response;

class UnauthorizedException extends BaseException
{
    public int $statusCode = Response::HTTP_UNAUTHORIZED;
}
