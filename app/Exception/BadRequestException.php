<?php

namespace App\Exeptions;

use Symfony\Component\HttpFoundation\Response;

class BadRequestException extends BaseException
{
    public int $statusCode = Response::HTTP_BAD_REQUEST;
}
