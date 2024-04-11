<?php

namespace App\Traits;

trait EnumToArray
{
    /**
     * Convert the enum to array
     *
     * @return array
     */
    public static function toArray(): array
    {
        return array_combine(self::names(), self::values());
    }

    /**
     * Get list names of the enum
     *
     * @return array
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Get list value of the enum
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}