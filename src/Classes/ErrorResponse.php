<?php
/**
 * By NacAL
 * nacer99@gmail.com
 */

namespace NacAL\Bounce\Classes;

use BadMethodCallException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Str;

/**
 * @method static error401($error)
 * @method static error404($error)
 * @method static error422($error)
 * @method static error400($error)
 */
class ErrorResponse
{
    /**
     * @return ResponseFactory|Response
     */
    public static function errorOccurred()
    {
        return static::error(__('errors.error_occurred'), 422);
    }

    /**
     * @param $error
     * @param int $status
     * @return ResponseFactory|Response
     */
    private static function error($error, int $status)
    {
        return response(['error' => $error], $status);
    }

    /**
     * @param $method
     * @param $arguments
     * @return ResponseFactory|Response
     */
    public static function __callStatic($method, $arguments)
    {
        $status = substr($method, 5);

        if (!Str::startsWith($method, 'error') || !is_numeric($status)) {
            throw new BadMethodCallException("Method [$method] does not exist on " . self::class);
        }

        return static::error($arguments[0], $status);
    }
}
