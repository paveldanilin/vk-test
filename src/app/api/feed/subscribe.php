<?php
namespace vk\app\api\feed;

use vk\app\message\SubscribeMessage;
use vk\lib\Context;
use vk\lib\http\exception\HttpBadRequestException;
use vk\lib\http\exception\HttpServerException;
use vk\lib\http\Request;
use vk\lib\messenger\DurableMessage;
use function vk\lib\messenger\publish_message;

/**
 * {
 *      "users": [123]
 * }
 */
return static function (Request $req, Context $context) {

    if (empty($req->getBody())) {
        throw new HttpBadRequestException('Body is empty');
    }

    $subscription_data = \json_decode($req->getBody(), true, 512, JSON_THROW_ON_ERROR);

    $subs_users = $subscription_data['users'] ?? [];

    if (($key = \array_search($context->getValue('user_id'), $subs_users, true)) !== false) {
        // удаляем свмого себя
        unset($subs_users[$key]);
    }
    if (empty($subs_users)) {
        throw new HttpBadRequestException('Not defined `users`');
    }

    $data = [
        'user_id' => $context->getValue('user_id'),
        'target_users' => $subs_users,
    ];

    $msg = new DurableMessage('user.subs', SubscribeMessage::fromArray($data));

    if (!publish_message($msg, $context)) {
        throw new HttpServerException('Could not publish message');
    }

    return '';
};
