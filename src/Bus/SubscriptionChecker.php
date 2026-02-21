<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Enum\SubscriptionType;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;

use function strrpos;
use function substr;

final readonly class SubscriptionChecker
{
    public function __construct(
        private CompleteActionStorage $completeActionStorage,
        private EventRelationStorage $eventRelationStorage,
    ) {}

    public function isSatisfied(Action $action): bool
    {
        return match ($action->getSubscriptionType()) {
            SubscriptionType::None => true,
            SubscriptionType::One => $this->isOnOneSatisfied($action),
            SubscriptionType::Any => $this->isOnAnySatisfied($action),
            SubscriptionType::All => $this->isOnAllSatisfied($action),
        };
    }

    private function isOnOneSatisfied(Action $action): bool
    {
        $eventId = $action->getOnOne();
        assert(null !== $eventId);

        return $this->isEventTriggered($eventId);
    }

    private function isOnAnySatisfied(Action $action): bool
    {
        return array_any($action->getOnAny(), fn($eventId) => $this->isEventTriggered($eventId));
    }

    private function isOnAllSatisfied(Action $action): bool
    {
        foreach ($action->getOnAll() as $eventId) {
            if (false === $this->isEventTriggered($eventId)) {
                return false;
            }
        }

        return [] !== $action->getOnAll();
    }

    private function isEventTriggered(string $eventId): bool
    {
        if ($this->eventRelationStorage->isExists($eventId)) {
            return true;
        }

        $subjectId = $this->extractSubjectId($eventId);
        $expectedStatus = $this->extractStatus($eventId);

        if (null === $expectedStatus) {
            return false;
        }

        if ($this->completeActionStorage->isExists($subjectId)) {
            $completeAction = $this->completeActionStorage->get($subjectId);

            return $completeAction->result->status === $expectedStatus;
        }

        return false;
    }

    private function extractSubjectId(string $eventId): string
    {
        $lastDelimiterPos = strrpos($eventId, IdFormatter::DELIMITER);

        if (false === $lastDelimiterPos) {
            return $eventId;
        }

        return substr($eventId, 0, $lastDelimiterPos);
    }

    private function extractStatus(string $eventId): ?ResultStatus
    {
        $lastDelimiterPos = strrpos($eventId, IdFormatter::DELIMITER);

        if (false === $lastDelimiterPos) {
            return null;
        }

        $statusString = substr($eventId, $lastDelimiterPos + strlen(IdFormatter::DELIMITER));

        foreach (ResultStatus::cases() as $status) {
            if ($status->value === $statusString) {
                return $status;
            }
        }

        return null;
    }
}
