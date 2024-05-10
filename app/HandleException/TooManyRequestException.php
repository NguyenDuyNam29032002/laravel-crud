<?php

namespace App\HandleException;

use Symfony\Component\HttpFoundation\Response;

class TooManyRequestException extends BaseException
{
    public int $statusCode = Response::HTTP_TOO_MANY_REQUESTS;
}
