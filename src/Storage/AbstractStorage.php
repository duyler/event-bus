<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Enum\ResultStatus;
use function array_key_exists;

abstract class AbstractStorage
{
    protected array $data = [];

    public function getAll(): array
    {
        return $this->data;
    }

    public function isExists(string $actionFullName): bool
    {
        return array_key_exists($actionFullName, $this->data);
    }

    public function remove(string $actionFullName): void
    {
        unset($actionFullName, $this->data);
    }

    protected function makeActionIdWithStatus(string $actionId, ResultStatus $status): string
    {
        return $actionId . '.' . $status->value;
    }
}
