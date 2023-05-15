<?php
declare(strict_types=1);


namespace Ortto\Connector\Helper;

class To
{
    public static function bool($mixed): bool
    {
        return (bool)filter_var($mixed, FILTER_VALIDATE_BOOLEAN);
    }

    public static function int($mixed): int
    {
        return (int)filter_var($mixed, FILTER_VALIDATE_INT);
    }

    public static function float($mixed): float
    {
        return (float)filter_var($mixed, FILTER_VALIDATE_FLOAT);
    }

    public static function email($mixed): string
    {
        return (string)filter_var($mixed, FILTER_SANITIZE_EMAIL);
    }

    public static function sqlDate(string $date): string
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }
}
