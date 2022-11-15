<?php
namespace vk\app\api;

use vk\lib\Context;
use vk\lib\http\Request;
use vk\lib\http\exception\HttpUnauthorizedException;

return static function (Request $request, Context $context, callable $next) {

    if (!$request->getHeaders()->has(USER_ID_HEADER)) {
        throw new HttpUnauthorizedException(
            \sprintf('A mandatory header is missed `%s`', USER_ID_HEADER)
        );
    }

    $context->setValue('user_id', $request->getHeaders()->getFirst(USER_ID_HEADER));

    return $next($request, $context);
};
