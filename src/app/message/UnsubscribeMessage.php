<?php
namespace vk\app\message;

final class UnsubscribeMessage implements \JsonSerializable
{
    private int $userId = 0;
    private int $targetUserId = 0;

    public static function fromArray(array $data): UnsubscribeMessage
    {
        $u = new self();
        $u->setUserId($data['user_id'] ?? 0);
        $u->setTargetUserId($data['target_user_id'] ?? 0);
        return $u;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getTargetUserId(): int
    {
        return $this->targetUserId;
    }

    public function setTargetUserId(int $targetUserId): void
    {
        $this->targetUserId = $targetUserId;
    }

    public function jsonSerialize()
    {
        return [
            'user_id' => $this->userId,
            'target_user_id' => $this->targetUserId,
        ];
    }
}
