<?php

declare(strict_types=1);

namespace Duyler\EventBus\Channel;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Service\QueueService;

#[Finalize]
final class Transfer
{
    /**
     * @var array<string, mixed[]>
     */
    private array $published = [];

    public function __construct(private readonly QueueService $queueService) {}

    public function push(string $channel, mixed $value): void
    {
        $this->published[$channel][] = $value;
    }

    public function has(string $channel): bool
    {
        return isset($this->published[$channel]);
    }

    public function isValid(): bool
    {
        return $this->queueService->isNotEmpty();
    }

    public function count(): int
    {
        return $this->queueService->count();
    }

    public function get(string $channel): mixed
    {
        return array_shift($this->published[$channel]);
    }

    public function finalize(): void
    {
        $this->published = [];
    }
}
