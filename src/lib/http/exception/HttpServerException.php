<?php
namespace vk\lib\http\exception;

final class HttpServerException extends HttpException
{
    public function __construct(string $message = 'Internal Server Error', int $status = 500)
    {
        parent::__construct($status, $message);
    }
}
