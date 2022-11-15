<?php
namespace vk\lib\messenger;

class MessengerException extends \RuntimeException
{
    public function __construct($message = "", \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
