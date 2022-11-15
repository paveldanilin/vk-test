<?php
namespace vk\app\message;

final class DeletePostMessage implements \JsonSerializable
{
    private int $userId;
    private int $postId;

    public static function fromArray(array $data): DeletePostMessage
    {
        $del_post = new self();
        $del_post->setUserId($data['user_id'] ?? 0);
        $del_post->setPostId($data['post_id'] ?? 0);
        return $del_post;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function jsonSerialize(): array
    {
        return [
            'user_id' => $this->getUserId(),
            'post_id' => $this->getPostId(),
        ];
    }
}
