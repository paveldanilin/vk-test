<?php
namespace vk\app\message;

final class NewPostMessage implements \JsonSerializable
{
    private ?int $postId = null;
    private int $userId = 0;
    private string $title = '';
    private string $content = '';

    public static function fromArray(array $data): NewPostMessage
    {
        $new_post = new self();
        $new_post->setPostId($data['post_id'] ?? null);
        $new_post->setUserId($data['user_id'] ?? 0);
        $new_post->setTitle($data['title'] ?? '');
        $new_post->setContent($data['content'] ?? '');
        return $new_post;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(?int $postId): void
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
            'post_id' => $this->getPostId(),
            'user_id' => $this->getUserId(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
        ];
    }
}
