<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Subscribe;
use RuntimeException;

use function array_key_exists;
use function array_walk;

class SubscribeStorage
{
    private array $subscribes = [];

    public function getSubscribers(string $subject): array
    {
        if (array_key_exists($subject, $this->subscribes)) {
            return $this->subscribes[$subject];
        }
        return [];
    }

    public function save(Subscribe $subscribe): void
    {
        if (array_key_exists($subscribe->subject, $this->subscribes)) {
            array_walk($this->subscribes[$subscribe->subject], function ($value) use ($subscribe) {
                if ($value->actionFullName === $subscribe->actionFullName) {
                    throw new RuntimeException('Subscribe ' . $subscribe->subject . ' already registered for ' . $subscribe->actionFullName);
                }
            });
        }

        $this->subscribes[$subscribe->subject][] = $subscribe;
    }

    public function getAll(): array
    {
        return $this->subscribes;
    }
}
