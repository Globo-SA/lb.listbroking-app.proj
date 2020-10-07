<?php

namespace ListBroking\AppBundle\Enum;

/**
 * HttpStatusCodeEnum
 */
class HttpStatusCodeEnum
{
    const HTTP_STATUS_CODE_CONTINUE              = 100;
    const HTTP_STATUS_CODE_OK                    = 200;
    const HTTP_STATUS_CODE_CREATED               = 201;
    const HTTP_STATUS_CODE_ACCEPTED              = 202;
    const HTTP_STATUS_CODE_MOVED_PERMANENTLY     = 301;
    const HTTP_STATUS_CODE_MOVED_TEMPORARILY     = 302;
    const HTTP_STATUS_CODE_NOT_MODIFIED          = 304;
    const HTTP_STATUS_CODE_TEMPORARY_REDIRECT    = 307;
    const HTTP_STATUS_CODE_PERMANENT_REDIRECT    = 308;
    const HTTP_STATUS_CODE_BAD_REQUEST           = 400;
    const HTTP_STATUS_CODE_UNAUTHORIZED          = 401;
    const HTTP_STATUS_CODE_FORBIDDEN             = 403;
    const HTTP_STATUS_CODE_NOT_FOUND             = 404;
    const HTTP_STATUS_CODE_METHOD_NOT_ALLOWED    = 405;
    const HTTP_STATUS_CODE_REQUEST_TIMEOUT       = 408;
    const HTTP_STATUS_CODE_GONE                  = 410;
    const HTTP_STATUS_CODE_PRECONDITION_REQUIRED = 428;
    const HTTP_STATUS_CODE_TOO_MANY_REQUESTS     = 429;
    const HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR = 500;
    const HTTP_STATUS_CODE_NOT_IMPLEMENTED       = 501;
    const HTTP_STATUS_CODE_BAD_GATEWAY           = 502;
    const HTTP_STATUS_CODE_SERVICE_UNAVAILABLE   = 503;
    const HTTP_STATUS_CODE_GATEWAY_TIMEOUT       = 504;

    /**
     * @return array|int[]
     */
    public static function getAll(): array
    {
        return [
            static::HTTP_STATUS_CODE_CONTINUE,
            static::HTTP_STATUS_CODE_OK,
            static::HTTP_STATUS_CODE_CREATED,
            static::HTTP_STATUS_CODE_ACCEPTED,
            static::HTTP_STATUS_CODE_MOVED_PERMANENTLY,
            static::HTTP_STATUS_CODE_MOVED_TEMPORARILY,
            static::HTTP_STATUS_CODE_NOT_MODIFIED,
            static::HTTP_STATUS_CODE_TEMPORARY_REDIRECT,
            static::HTTP_STATUS_CODE_PERMANENT_REDIRECT,
            static::HTTP_STATUS_CODE_BAD_REQUEST,
            static::HTTP_STATUS_CODE_UNAUTHORIZED,
            static::HTTP_STATUS_CODE_FORBIDDEN,
            static::HTTP_STATUS_CODE_NOT_FOUND,
            static::HTTP_STATUS_CODE_METHOD_NOT_ALLOWED,
            static::HTTP_STATUS_CODE_REQUEST_TIMEOUT,
            static::HTTP_STATUS_CODE_GONE,
            static::HTTP_STATUS_CODE_PRECONDITION_REQUIRED,
            static::HTTP_STATUS_CODE_TOO_MANY_REQUESTS,
            static::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR,
            static::HTTP_STATUS_CODE_NOT_IMPLEMENTED,
            static::HTTP_STATUS_CODE_BAD_GATEWAY,
            static::HTTP_STATUS_CODE_SERVICE_UNAVAILABLE,
            static::HTTP_STATUS_CODE_GATEWAY_TIMEOUT,
        ];
    }
}
