<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Enum\SubscriptionType;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Storage\CompleteActorStorage;
use Duyler\EventBus\Storage\EventRelationStorage;

use function strrpos;
use function substr;

final readonly class SubscriptionChecker
{
    public function __construct(
        private CompleteActorStorage $completeActorStorage,
        private EventRelationStorage $eventRelationStorage,
    ) {}

    public function isSatisfied(Actor $actor): bool
    {
        return match ($actor->getSubscriptionType()) {
            SubscriptionType::None => true,
            SubscriptionType::One => $this->isOnOneSatisfied($actor),
            SubscriptionType::Any => $this->isOnAnySatisfied($actor),
            SubscriptionType::All => $this->isOnAllSatisfied($actor),
        };
    }

    private function isOnOneSatisfied(Actor $actor): bool
    {
        $eventId = $actor->getOnOne();
        assert(null !== $eventId);

        return $this->isEventTriggered($eventId);
    }

    private function isOnAnySatisfied(Actor $actor): bool
    {
        return array_any($actor->getOnAny(), fn($eventId) => $this->isEventTriggered($eventId));
    }

    private function isOnAllSatisfied(Actor $actor): bool
    {
        foreach ($actor->getOnAll() as $eventId) {
            if (false === $this->isEventTriggered($eventId)) {
                return false;
            }
        }

        return [] !== $actor->getOnAll();
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

        if ($this->completeActorStorage->isExists($subjectId)) {
            $completeActor = $this->completeActorStorage->get($subjectId);

            return $completeActor->result->status === $expectedStatus;
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
