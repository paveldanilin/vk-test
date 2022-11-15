<?php
namespace vk\lib\http\exception;

require_once __DIR__ . '/HttpUnauthorizedException.php';
require_once __DIR__ . '/HttpServerException.php';
require_once __DIR__ . '/HttpBadRequestException.php';

class HttpException extends \RuntimeException
{
    private int $status;

    public function __construct(int $status, $message)
    {
        parent::__construct($message);
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
