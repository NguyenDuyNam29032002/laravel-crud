<?php

namespace App\Exeptions;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends BaseException
{
    public int $statusCode = Response::HTTP_NOT_FOUND;
}
