<?php
namespace vk\app\api\post;

use vk\app\message\EditPostMessage;
use vk\lib\Context;
use vk\lib\http\exception\HttpBadRequestException;
use vk\lib\http\exception\HttpServerException;
use vk\lib\http\Request;
use vk\lib\messenger\DurableMessage;
use function vk\lib\messenger\publish_message;


return static function (Request $req, Context $context) {

    if (empty($req->getBody())) {
        throw new HttpBadRequestException('Body is empty');
    }

    $input = \json_decode($req->getBody(), true, 512, JSON_THROW_ON_ERROR);

    $post_id = $input['post_id'] ?? -1;
    if ($post_id < 0) {
        throw new HttpBadRequestException('Not defined `post_id`');
    }

    $edit_post = new EditPostMessage();
    $edit_post->setPostId($post_id);
    $edit_post->setUserId($context->getValue('user_id'));
    $edit_post->setTitle($input['title'] ?? '');
    $edit_post->setContent($input['content'] ?? '');

    $msg = new DurableMessage('user.posts', $edit_post);

    if (!publish_message($msg, $context)) {
        throw new HttpServerException('Could not publish message');
    }

    return '';
};
