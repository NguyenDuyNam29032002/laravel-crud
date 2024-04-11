<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait HasRequest
{
    /**
     * @param object $request
     * @param array  $params
     *
     * @return object
     */
    public function removeRequestParams(object $request, array $params): object
    {
        $isHTTPRequest = $request instanceof Request;

        foreach ($params as $param) {
            if ($isHTTPRequest) {
                $request->request->remove($param);
            }
            else {
                unset($request->{$param});
            }
        }

        return $request;
    }

    /**
     * @param object $request
     * @param array  $params
     *
     * @return object
     */

    public function mergeRequestParams(object $request, array $params): object
    {

        if ($request instanceof Request) {
            $request->merge($params);
        }
        else {
            $request = (object)array_merge((array)$request, $params);
        }

        return $request;
    }

    /**
     * @param object $request
     *
     * @return array
     */
    public function convertRequestToArray(object $request): array
    {
        return ($request instanceof Request || $request instanceof Model) ? $request->toArray() : (array)$request;
    }
}
