<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Dto\Subscribe;
use Duyler\EventBus\Enum\ResultStatus;
use RuntimeException;

use function array_key_exists;
use function array_intersect_key;
use function array_flip;
use function preg_quote;
use function preg_grep;
use function array_keys;

class SubscribeStorage extends AbstractStorage
{
    public function save(Subscribe $subscribe): void
    {
        $id = $subscribe->subjectId . '.' . $subscribe->status->value . '@' . $subscribe->actionId;

        if (array_key_exists($id, $this->data)) {
            throw new RuntimeException(
            'Subscribe ' . $id . ' already registered for ' . $subscribe->actionId
            );
        }

        $this->data[$id] = $subscribe;
    }

    /**
     * @return Subscribe[]
     */
    public function getSubscribers(string $actionId, ResultStatus $status): array
    {
        $pattern = '/' . preg_quote($this->makeActionIdWithStatus($actionId, $status) . '@') . '/';

        return array_intersect_key(
            $this->data, array_flip(
                preg_grep($pattern, array_keys($this->data))
            )
        );
    }
}
