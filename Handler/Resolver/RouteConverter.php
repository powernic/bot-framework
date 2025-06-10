<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Resolver;

class RouteConverter
{
    public function convertMaskToRegex(string $mask): string
    {
        // Replace placeholders with a regex pattern that matches only digits or letters
        $pattern = preg_replace([
            '/{[^}]+<\\\d\+>}/',
            '/{[^}]+}/'
        ], [
            '(\\d+)',
            '([0-9a-zA-Z,]+)'
        ], $mask);
        return '/^' . str_replace('/', '\/', $pattern) . '$/';
    }
}
