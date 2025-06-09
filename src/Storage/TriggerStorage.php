<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use function array_flip;
use function array_intersect_key;
use function array_key_exists;
use function array_keys;

use Duyler\EventBus\Build\Trigger;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;

use function preg_grep;
use function preg_quote;

class TriggerStorage
{
    /**
     * @var array<string, Trigger>
     */
    private array $data = [];

    /** @var array<string, array<array-key, Trigger>> */
    private array $byActionId = [];

    public function save(Trigger $trigger): void
    {
        $id = $this->makeTriggerId($trigger);

        $this->data[$id] = $trigger;
        $this->byActionId[$trigger->actionId][] = $trigger;
    }

    public function isExists(Trigger $trigger): bool
    {
        $id = $this->makeTriggerId($trigger);

        return array_key_exists($id, $this->data);
    }

    /**
     * @return Trigger[]
     */
    public function getTriggers(string $actionId, ResultStatus $status): array
    {
        $pattern = '/^' . preg_quote($this->makeActionIdWithStatus($actionId, $status) . '@') . '/';

        return array_intersect_key(
            $this->data,
            array_flip(
                preg_grep($pattern, array_keys($this->data)),
            ),
        );
    }

    public function removeByActionId(string $actionId): void
    {
        $triggers = $this->byActionId[$actionId] ?? [];

        foreach ($triggers as $trigger) {
            unset($this->data[$this->makeTriggerId($trigger)]);
        }

        unset($this->byActionId[$actionId]);
    }

    public function remove(Trigger $trigger): void
    {
        $id = $this->makeTriggerId($trigger);
        unset($this->data[$id]);
        unset($this->byActionId[$trigger->actionId]);
    }

    private function makeActionIdWithStatus(string $actionId, ResultStatus $status): string
    {
        return $actionId . IdFormatter::DELIMITER . $status->value;
    }

    private function makeTriggerId(Trigger $trigger): string
    {
        return $trigger->subjectId
            . IdFormatter::DELIMITER . $trigger->status->value
            . '@' . $trigger->actionId;
    }

    public function getAll(): array
    {
        return $this->data;
    }
}
