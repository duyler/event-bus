<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Dto\Result;

#[Finalize(method: 'reset')]
class CompleteActionStorage
{
    /**
     * @var array<string, CompleteAction>
     */
    private array $data = [];

    /**
     * @var array<string, CompleteAction>
     */
    private array $byTypeIdAllowed = [];

    public function save(CompleteAction $completeAction): void
    {
        $this->data[$completeAction->action->getId()] = $completeAction;

        $type = $completeAction->action->getTypeId();

        if (null !== $type) {
            if (false === $completeAction->action->isPrivate()) {
                $this->byTypeIdAllowed[$type] = $completeAction;
            }
        }
    }

    /**
     * @return array<string, CompleteAction>
     */
    public function getAllAllowedByTypeArray(array $array, string $actionId): array
    {
        $withoutPrivate = array_intersect_key($this->byTypeIdAllowed, array_flip($array));

        $allowed = [];

        foreach ($withoutPrivate as $type => $completeAction) {
            if (0 < count($completeAction->action->getSealed())) {
                if (in_array($actionId, $completeAction->action->getSealed())) {
                    $allowed[$type] = $completeAction;
                }
            } else {
                $allowed[$type] = $completeAction;
            }
        }

        return $allowed;
    }

    /**
     * @return array<string, CompleteAction>
     */
    public function getAllByArray(array $array): array
    {
        return array_intersect_key($this->data, array_flip($array));
    }

    public function getResult(string $actionId): Result
    {
        return $this->data[$actionId]->result;
    }

    public function get(string $actionId): CompleteAction
    {
        return $this->data[$actionId];
    }

    public function isExists(string $actionId): bool
    {
        return array_key_exists($actionId, $this->data);
    }

    /**
     * @return array<string, CompleteAction>
     */
    public function getAll(): array
    {
        return $this->data;
    }

    public function reset(): void
    {
        $this->data = [];
        $this->byTypeIdAllowed = [];
    }

    public function remove(string $actionId): void
    {
        unset($this->data[$actionId]);
    }
}
