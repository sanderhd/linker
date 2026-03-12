<?php

class Validator
{
    // schoonmaken om xss te voorkomen
    public static function sanitize($value)
    {
        return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }

    // email validatie
    public static function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    // url validatie
    public static function isUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    // minimum lengte validatie
    public static function minLength($value, $min)
    {
        return mb_strlen($value) >= $min;
    }

    // maximum lengte validatie
    public static function maxLength($value, $max)
    {
        return mb_strlen($value) <= $max;
    }

    // required veld validatie
    public static function required($value)
    {
        if (is_array($value)) return !empty($value);
        return trim((string)$value) !== '';
    }
}
