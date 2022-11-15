<?php
namespace vk\app\api\feed;

use vk\app\handler\UnsubscribeHandler;
use vk\app\message\SubscribeMessage;
use vk\app\message\UnsubscribeMessage;
use vk\lib\Context;
use vk\lib\http\exception\HttpBadRequestException;
use vk\lib\http\exception\HttpServerException;
use vk\lib\http\Request;
use vk\lib\messenger\DurableMessage;
use function vk\lib\messenger\publish_message;

/**
 * {
 *      "user_id": 123
 * }
 */
return static function (Request $req, Context $context) {

    if (empty($req->getBody())) {
        throw new HttpBadRequestException('Body is empty');
    }

    $subscription_data = \json_decode($req->getBody(), true, 512, JSON_THROW_ON_ERROR);

    $subs_user_id = $subscription_data['user_id'] ?? 0;

    if (empty($subs_user_id)) {
        throw new HttpBadRequestException('Not defined `user_id`');
    }

    if ($subs_user_id == $context->getValue('user_id')) {
        throw new HttpBadRequestException('Its you!');
    }

    $data = [
        'user_id' => $context->getValue('user_id'),
        'target_user_id' => $subs_user_id,
    ];

    $msg = new DurableMessage('user.subs', UnsubscribeMessage::fromArray($data));

    if (!publish_message($msg, $context)) {
        throw new HttpServerException('Could not publish message');
    }

    return '';
};
