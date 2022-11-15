<?php
namespace vk\app\module\user_feed;

use vk\lib\Context;
use function vk\lib\db\db_get_conn;

function user_get_subscriptions(int $user_id, Context $context): array
{
    $conn = db_get_conn('user_database', $context);

    $ret = $conn->query('SELECT subs_user_id FROM user_subscription WHERE user_id = ' . $user_id . ' AND deleted_at IS NULL');
    if (false === $ret) {
        $conn->close();
        return [];
    }
    $db_user_subs = $ret->fetch_all();
    $ret = [];
    foreach ($db_user_subs as $entry) {
        $ret[] = (int)($entry[0] ?? 0);
    }
    $conn->close();
    return $ret;
}
