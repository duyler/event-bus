<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Dto\Coroutine;

class CoroutineStorage extends AbstractStorage
{
    public function save(Coroutine $coroutine)
    {
        $this->data[$coroutine->id] = $coroutine;
    }

    public function get(string $id): ?Coroutine
    {
        return $this->data[$id] ?? null;
    }

    public function isExists(string $id): bool
    {
        return array_key_exists($id, $this->data);
    }
}
