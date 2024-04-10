<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait HasRquest
{
    /**
     * @param object $request
     * @param array $params
     *
     * @return object
     */
    public function RemoveRequestParams(object $request, array $params)
    {
        $isHTTPRequest = $request instanceof Request;

        foreach ($params as $param) {
            if ($isHTTPRequest) {
                $request->request->remove($param);
            } else {
                unset($request->{$param});
            }
        }
        return $request;
    }
    /**
     * @param object $request
     * @param array $params
     *
     * @return object
     */

    public function mergeRequestParams(object $request, array $params)
    {
        $isHTTPRequest = $request instanceof Request;

        if ($isHTTPRequest) {
            $request->merge($params);
        } else {
            $request = (object) array_merge((array) $request, $params);
        }

        return $request;
    }
    /**
     * @param object $request
     *
     * @return array
     */
    public function convertRequestToArray(object $request)
    {
        return ($request instanceof Request || $request instanceof Model) ? $request->toArray() : (array) $request;
    }
}
