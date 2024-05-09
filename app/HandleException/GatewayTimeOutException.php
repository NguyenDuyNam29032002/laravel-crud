<?php

namespace App\Exeptions;

use Symfony\Component\HttpFoundation\Response;

class GatewayTimeOutException extends BaseException
{
    public int $statusCode = Response::HTTP_GATEWAY_TIMEOUT;
}
