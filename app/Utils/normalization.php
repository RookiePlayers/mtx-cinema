<?php

namespace App\Utils;

class Normalization{
    public static function normalizeKeysFromOMDBApi(array $movieData): array
    {
        $keys = array_keys($movieData);
        $normalizedData = [];
        foreach ($keys as $key) {
            $normalizedKey = self::toCamelCase($key);
            $normalizedData[$normalizedKey] = $movieData[$key];
        }
        return $normalizedData;

    }
    private static function toCamelCase(string $string): string
    {
        $string = preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', ' ', $string);
        $string = preg_replace('/(?<=[A-Z])(?=[A-Z][a-z])/', ' ', $string);
        $string = str_replace(['-', '_'], ' ', $string);
        $string = strtolower($string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);

        return lcfirst($string);
    }
}
