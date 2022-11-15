<?php
namespace vk\app\api\post;

use vk\app\message\NewPostMessage;
use vk\lib\Context;
use vk\lib\http\exception\HttpBadRequestException;
use vk\lib\http\exception\HttpServerException;
use vk\lib\http\Request;
use vk\lib\messenger\DurableMessage;
use function vk\lib\messenger\publish_message;
use function vk\lib\util\id_create;


return static function (Request $req, Context $context) {

    if (empty($req->getBody())) {
        throw new HttpBadRequestException('Body is empty');
    }

    $input = \json_decode($req->getBody(), true, 512, JSON_THROW_ON_ERROR);
    $input['user_id'] = $context->getValue('user_id');
    $input['post_id'] = id_create($context->getValue('user_id'));

    $new_post = NewPostMessage::fromArray($input);

    $msg = new DurableMessage('user.posts', $new_post);

    if (!publish_message($msg, $context)) {
        throw new HttpServerException('Could not publish message');
    }

    return [
        'post_id' => $new_post->getPostId(),
    ];
};
