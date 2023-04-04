<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Dto\Subscribe;
use Duyler\EventBus\Enum\ResultStatus;
use RuntimeException;

use function array_key_exists;
use function array_walk;

class SubscribeStorage extends AbstractStorage
{
    /**
     * @return Subscribe[]
     */
    public function getSubscribers(string $actionId, ResultStatus $status): array
    {
        $subject = $this->makeActionIdWithStatus($actionId, $status);

        if (array_key_exists($subject, $this->data)) {
            return $this->data[$subject];
        }
        return [];
    }

    public function save(Subscribe $subscribe): void
    {
        $subjectId = $this->makeActionIdWithStatus($subscribe->subjectId, $subscribe->status);

        if (array_key_exists($subjectId, $this->data)) {
            array_walk($this->data[$subjectId], function ($value) use ($subscribe, $subjectId) {
                if ($value->actionId === $subscribe->actionId) {
                    throw new RuntimeException(
                        'Subscribe ' . $subjectId . ' already registered for ' . $subscribe->actionId
                    );
                }
            });
        }

        $this->data[$subjectId][] = $subscribe;
    }
}
