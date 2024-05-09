<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum TestEnum: string
{
    use EnumToArray;
}