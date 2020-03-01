<?php
/**
 * Created by PhpStorm.
 * User: zzpzero
 * Date: 2016/10/20
 * Time: 15:30
 */

namespace Jasmine\util;


class Str
{

    /**
     * @param $value
     * @return mixed
     */
    static public function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string $value
     * @param string $delimiter
     * @return string
     */
    static public function snake($value, $delimiter = '_')
    {
        $value = self::value($value);

        return ctype_lower($value) ? $value : strtolower(preg_replace('/(.)([A-Z])/', '$1' . $delimiter . '$2', $value));
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    static public function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) return true;
        }

        return false;
    }


    /**
     * Convert a value to camel case.
     *
     * @param  string $value
     * @return string
     */
    static public function camel($value)
    {
        $value = self::value($value);

        return lcfirst(static::studly($value));
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    static public function contains($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false) return true;
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    static public function endsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle == substr($haystack, -strlen($needle))) return true;
        }

        return false;
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string $value
     * @param  string $cap
     * @return mixed
     */
    static public function finish($value, $cap)
    {
        $value = self::value($value);

        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:' . $quoted . ')+$/', '', $value) . $cap;
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string $pattern
     * @param  string $value
     * @return bool
     */
    static public function is($pattern, $value)
    {
        $value = self::value($value);

        if ($pattern == $value) return true;

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern) . '\z';

        return (bool)preg_match('#^' . $pattern . '#', $value);
    }

    /**
     * Return the length of the given string.
     *
     * @param  string $value
     * @return int
     */
    static public function length($value)
    {
        $value = self::value($value);

        return mb_strlen($value);
    }

    /**
     * Limit the number of characters in a string.
     * @param $value
     * @param int $limit
     * @param mixed $end
     * @return mixed|string
     */
    static public function limit($value, $limit = 100, $end = '')
    {
        $value = self::value($value);

        if (mb_strlen($value) <= $limit) return $value;

        return rtrim(mb_substr($value, 0, $limit, 'UTF-8')) . $end;
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param  string $value
     * @return string
     */
    static public function lower($value)
    {
        return mb_strtolower(self::value($value));
    }

    /**
     * Limit the number of words in a string.
     *
     * @param  string $value
     * @param  int $words
     * @param  mixed $end
     * @return mixed
     */
    static public function words($value, $words = 100, $end = '')
    {
        $value = self::value($value);

        preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches);

        if (!isset($matches[0])) return $value;

        if (strlen($value) == strlen($matches[0])) return $value;

        return rtrim($matches[0]) . $end;
    }

    /**
     * Parse a Class@method  callback into class and method.
     *
     * @param  string $callback
     * @param  string $default
     * @return array
     */
    static public function parseCallback($callback, $default)
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : array($callback, $default);
    }


    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int $length
     * @return string
     *
     * @throws \RuntimeException
     */
    static public function random($length = 16)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length * 2);

            if ($bytes === false) {
                throw new \RuntimeException('Unable to generate random string.');
            }

            return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
        }

        return static::quickRandom($length);
    }

    /**
     * Generate a "random" alpha-numeric string.
     *
     * Should not be considered sufficient for cryptography, etc.
     *
     * @param  int $length
     * @return string
     */
    static public function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param  string $value
     * @return string
     */
    static public function upper($value)
    {
        return mb_strtoupper(self::value($value));
    }

    /**
     * Convert the given string to title case.
     *
     * @param  string $value
     * @return string
     */
    static public function title($value)
    {
        return mb_convert_case(self::value($value), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string $value
     * @return string
     */
    static public function studly($value)
    {
        self::value($value);

        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * Returns the portion of string specified by the start and length parameters.
     *
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * @return string
     */
    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }
}