<?php
namespace vk\lib\http\exception;

final class HttpBadRequestException extends HttpException
{
    public function __construct(string $message = 'Bad request', int $status = 400)
    {
        parent::__construct($status, $message);
    }
}
