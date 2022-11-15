<?php
namespace vk\app\message;

final class EditPostMessage implements \JsonSerializable
{
    private int $postId = 0;
    private int $userId = 0;
    private string $title = '';
    private string $content = '';

    public static function fromArray(array $data): EditPostMessage
    {
        $edit_post = new self();
        $edit_post->setPostId($data['post_id'] ?? 0);
        $edit_post->setUserId($data['user_id'] ?? 0);
        $edit_post->setTitle($data['title'] ?? '');
        $edit_post->setContent($data['content'] ?? '');
        return $edit_post;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function jsonSerialize(): array
    {
        return [
            'user_id' => $this->getUserId(),
            'post_id' => $this->getPostId(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
        ];
    }
}
