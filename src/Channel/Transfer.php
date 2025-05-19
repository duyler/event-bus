<?php

declare(strict_types=1);

namespace Duyler\EventBus\Channel;

use Duyler\EventBus\Service\QueueService;
use Duyler\EventBus\Storage\MessageStorage;

final class Transfer
{
    public function __construct(
        private MessageStorage $messageStorage,
        private QueueService $queueService,
    ) {}

    public function push(Message $message): void
    {
        $this->messageStorage->set($message);
    }

    public function has(string $channel, string $tag): bool
    {
        return $this->messageStorage->has($channel, $tag);
    }

    public function isValid(): bool
    {
        return $this->queueService->isNotEmpty();
    }

    public function get(string $channel, string $tag): Message
    {
        return $this->messageStorage->get($channel, $tag);
    }
}
