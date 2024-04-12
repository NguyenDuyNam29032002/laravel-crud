<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum TypeEnums: string
{
    use EnumToArray;

    case CREATE = 'create';
    case UPDATE = 'update';
}
