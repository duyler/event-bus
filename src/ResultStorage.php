<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Result;

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
}
