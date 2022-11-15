<?php
namespace vk\app\handler;

use vk\app\message\UnsubscribeMessage;
use vk\lib\Context;

final class UnsubscribeHandler
{
    public function __invoke(UnsubscribeMessage $message, Context $context): void
    {
        // TODO: не успел :)
    }
}
