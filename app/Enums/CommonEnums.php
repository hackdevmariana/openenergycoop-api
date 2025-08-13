<?php

namespace App\Enums;

class ProfileTypes
{
    const INDIVIDUAL = 'individual';
    const TENANT = 'tenant';
    const COMPANY = 'company';
    const OWNERSHIP_CHANGE = 'ownership_change';
    
    public static function all(): array
    {
        return [
            self::INDIVIDUAL,
            self::TENANT,
            self::COMPANY,
            self::OWNERSHIP_CHANGE,
        ];
    }
}
