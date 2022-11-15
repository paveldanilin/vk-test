<?php
namespace vk\app\handler;


use vk\app\message\SubscribeMessage;
use vk\lib\Context;
use function vk\lib\cache\cache_key;
use function vk\lib\cache\cache_set_json;
use function vk\lib\db\db_get_conn;

final class SubscribeHandler
{
    public function __invoke(SubscribeMessage $message, Context $context): void
    {
        $this->persist($message, $context);
    }

    private function persist(SubscribeMessage $message, Context $context): void
    {
        $user_id = $message->getUserId();
        $subs_users = $message->getTargetUsers();

        $conn = db_get_conn('user_database', $context);

        $ret = $conn->query('SELECT subs_user_id FROM user_subscription WHERE user_id = ' . $user_id . ' AND deleted_at IS NULL');
        if (false === $ret) {
            echo 'DB: ' . $conn->error . "\n";
            $conn->close();
            return;
        }

        $db_user_subs = $ret->fetch_all();
        $tmp = [];
        foreach ($db_user_subs as $entry) {
            $tmp[] = (int)($entry[0] ?? 0);
        }
        $db_user_subs = $tmp;
        unset($tmp);

        foreach ($subs_users as $idx => $subs_user) {
            if (\in_array($subs_user, $db_user_subs)) {
                unset($subs_users[$idx]);
            }
        }

        if (empty($subs_users)) {
            echo "Nothing to do \n";
            $conn->close();
            return;
        }

        $stmt = $conn->prepare('INSERT INTO user_subscription (`user_id`, `subs_user_id`) VALUES (?,?)');

        foreach ($subs_users as $subs_user) {
            $stmt->bind_param('ii', $user_id, $subs_user);
            $stmt->execute();
        }

        $stmt->close();
        $conn->close();

        // cache
        $final_subs = \array_merge($db_user_subs, $subs_users);
        $key = cache_key($context, 'user_subs', 3600);
        cache_set_json($key, $final_subs, $context);
    }
}
