<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Result;

use function array_key_exists;

class ResultStorage
{
    private array $data = [];

    public function save(string $id, Result $result): void
    {
        $this->data[$id] = $result;
    }

    public function getAllByArray(array $required): array
    {
        $data = [];

        foreach ($required as $serviceId) {
            $data[$serviceId] = $this->data[$serviceId]->data;
        }

        return $data;
    }

    public function getResult(string $id): Result
    {
        return $this->data[$id];
    }

    public function isExists(string $id): bool
    {
        return array_key_exists($id, $this->data);
    }
}
