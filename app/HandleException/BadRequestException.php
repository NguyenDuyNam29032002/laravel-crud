<?php

namespace App\HandleException;

use Symfony\Component\HttpFoundation\Response;

class BadRequestException extends BaseException
{
    public int $statusCode = Response::HTTP_BAD_REQUEST;
}
