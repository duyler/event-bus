<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use DateTimeImmutable;
use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Channel\Message;

#[Finalize(method: 'cleanUp')]
final class MessageStorage
{
    /** @var array<string, array<string, Message>> $data */
    private array $data = [];

    /** @var array<array-key, Message> $messages */
    private array $messages = [];

    public function set(Message $message): void
    {
        $this->data[$message->getChannel()][$message->getTag()] = $message;
        $this->messages[] = $message;
    }

    public function has(string $channel, string $tag): bool
    {
        return isset($this->data[$channel][$tag]);
    }

    public function get(string $channel, string $tag): Message
    {
        return $this->data[$channel][$tag];
    }

    public function recount(): void
    {
        foreach ($this->messages as $key => $message) {
            if ($message->getTtl() <= new DateTimeImmutable()) {
                unset($this->data[$message->getChannel()][$message->getTag()]);
                unset($this->messages[$key]);
            }
        }
    }

    public function cleanUp(): void
    {
        $this->data = [];
        $this->messages = [];
    }
}
