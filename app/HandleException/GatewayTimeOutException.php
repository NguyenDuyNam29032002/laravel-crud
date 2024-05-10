<?php

namespace App\HandleException;

use Symfony\Component\HttpFoundation\Response;

class GatewayTimeOutException extends BaseException
{
    public int $statusCode = Response::HTTP_GATEWAY_TIMEOUT;
}
