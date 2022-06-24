<?php

declare(strict_types=1);

namespace Konveyer\EventBus\Storage;

use Konveyer\EventBus\DTO\Result;
use Konveyer\EventBus\DTO\Subscribe;
use Konveyer\EventBus\ActionIdBuilder;
use Konveyer\EventBus\Task;
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
        $subjectId = ActionIdBuilder::bySubscribe($subscribe);

        if (array_key_exists($subjectId, $this->subscribes)) {
            array_walk($this->subscribes[$subjectId], function ($value) use ($subscribe, $subjectId) {
                if ($value->actionFullName === $subscribe->actionFullName) {
                    throw new RuntimeException(
                        'Subscribe ' . $subjectId . ' already registered for ' . $subscribe->actionFullName
                    );
                }
            });
        }

        $this->subscribes[$subjectId][] = $subscribe;
    }

    public function getAll(): array
    {
        return $this->subscribes;
    }
}
