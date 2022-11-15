<?php
namespace vk\lib\http\exception;

final class HttpUnauthorizedException extends HttpException
{
    public function __construct(string $message = 'Unauthorized', int $status = 401)
    {
        parent::__construct($status, $message);
    }
}
