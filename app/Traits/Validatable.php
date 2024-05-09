<?php

namespace App\Traits;

use App\HandleException\BadRequestException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait Validatable
{
    /**
     * @description validate incoming request
     * @param object $request
     * @param array  $rules
     * @param array  $messages
     * @param array  $customAttributes
     * @param bool   $throwable
     *
     * @return bool|array
     * @throws BadRequestException
     */
    public function doValidate(object $request, array $rules = [], array $messages = [], array $customAttributes = [],
                               bool   $throwable = true): bool|array
    {
        if ($request instanceof Request || $request instanceof Model) {
            $request = $request->toArray();
        }
        else {
            $request = (array)$request;
        }
        $validator = Validator::make($request, $rules, $messages, $customAttributes);

        // If you have no rules were violated

        if (!$validator->fails()) {
            return true;
        }

        if ($throwable) {
            throw new BadRequestException('request not invalid', messages: $validator->errors()->toArray());
        }
        else {
            return $validator->errors()->toArray();
        }
    }

    /**
     * @description validate store request
     * @param object $request
     * @param array  $rules
     * @param array  $messages
     * @param array  $customAttributes
     * @param bool   $throwable
     *
     * @return bool|array
     * @throws BadRequestException
     */
    public function validateStoreRequest(object $request, array $rules = [], array $messages = [],
                                         array  $customAttributes = [], bool $throwable = true): bool|array
    {
        if (!$rules) {
            return true;
        }

        return $this->doValidate($request, $rules, $messages, $customAttributes, $throwable);
    }

    /**
     * @description validate update request
     * @param int|string $id
     * @param object     $request
     * @param array      $rules
     * @param array      $messages
     * @param array      $customAttributes
     * @param bool       $throwable
     *
     * @return bool|array
     * @throws BadRequestException
     */
    public function validateUpdateRequest(int|string $id, object $request, array $rules = [], array $messages = [],
                                          array      $customAttributes = [], bool $throwable = true): bool|array
    {
        if (!$rules || !$id) {
            return true;
        }

        return $this->doValidate($request, $rules, $messages, $customAttributes, $throwable);
    }
}