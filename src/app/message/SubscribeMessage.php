<?php
namespace vk\app\message;

final class SubscribeMessage implements \JsonSerializable
{
    private int $userId = 0;
    private array $targetUsers = [];

    public static function fromArray(array $data): SubscribeMessage
    {
        $s = new self();
        $s->setUserId($data['user_id'] ?? 0);
        $s->setTargetUsers($data['target_users'] ?? 0);
        return $s;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getTargetUsers(): array
    {
        return $this->targetUsers;
    }

    public function setTargetUsers(array $targetUsers): void
    {
        $this->targetUsers = $targetUsers;
    }

    public function jsonSerialize()
    {
        return [
            'user_id' => $this->userId,
            'target_users' => $this->targetUsers,
        ];
    }
}
