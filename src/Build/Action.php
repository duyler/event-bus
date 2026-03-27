<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Closure;
use DateInterval;
use Duyler\EventBus\Bus\Action as InternalAction;
use Duyler\EventBus\Enum\SubscriptionType;
use Duyler\EventBus\Exception\InvalidSubscriptionCombinationException;
use Duyler\EventBus\Formatter\IdFormatter;
use JsonSerializable;
use Override;
use UnitEnum;

final readonly class Action implements JsonSerializable
{
    public function __construct(
        public string|UnitEnum $id,
        public string|Closure $handler,
        public ?string $description = null,
        public string|Id|null $onOne = null,

        /** @var array<array-key, string|Id> */
        public array $onAny = [],

        /** @var array<array-key, string|Id> */
        public array $onAll = [],

        /** @var array<array-key, string|UnitEnum> */
        public array $required = [],

        /** @var array<string, string> */
        public array $bind = [],

        /** @var array<string, string> */
        public array $providers = [],

        /** @var array<string, array<string, mixed>> */
        public array $definitions = [],
        public ?string $argument = null,

        /** @var class-string|Closure|null */
        public string|Closure|null $argumentFactory = null,

        /** @var class-string|null */
        public ?string $context = null,

        /** @var class-string|null */
        public ?string $type = null,

        /** @var class-string|null */
        public ?string $typeCollection = null,
        public bool $immutable = true,
        public string|Closure|null $rollback = null,
        public bool $externalAccess = true,
        public bool $repeatable = true,
        public bool $lock = true,
        public bool $private = false,

        /** @var array<array-key, string|UnitEnum> */
        public array $sealed = [],
        public bool $silent = false,

        /** @var array<array-key, string|UnitEnum> */
        public array $fallbacks = [],
        public int $retries = 0,
        public ?DateInterval $retryDelay = null,

        /** @var array<string|int, mixed> */
        public array $attributes = [],
    ) {
        $this->validateSubscription();
    }

    private function validateSubscription(): void
    {
        $subscriptionTypes = [];

        if (null !== $this->onOne) {
            $subscriptionTypes[] = SubscriptionType::One;
        }

        if ([] !== $this->onAny) {
            $subscriptionTypes[] = SubscriptionType::Any;
        }

        if ([] !== $this->onAll) {
            $subscriptionTypes[] = SubscriptionType::All;
        }

        if (count($subscriptionTypes) > 1) {
            throw new InvalidSubscriptionCombinationException($subscriptionTypes);
        }
    }

    public static function fromInternal(InternalAction $internalAction): Action
    {
        return new static(
            id: $internalAction->getExternalId(),
            handler: $internalAction->getHandler(),
            description: $internalAction->getDescription(),
            onOne: $internalAction->getOnOne(),
            onAny: $internalAction->getOnAny(),
            onAll: $internalAction->getOnAll(),
            required: $internalAction->getExternalRequired(),
            bind: $internalAction->getBind(),
            providers: $internalAction->getProviders(),
            definitions: $internalAction->getDefinitions(),
            argument: $internalAction->getArgument(),
            argumentFactory: $internalAction->getArgumentFactory(),
            context: $internalAction->getContext(),
            type: $internalAction->getType(),
            typeCollection: $internalAction->getTypeCollection(),
            immutable: $internalAction->isImmutable(),
            rollback: $internalAction->getRollback(),
            externalAccess: $internalAction->isExternalAccess(),
            repeatable: $internalAction->isRepeatable(),
            lock: $internalAction->isLock(),
            private: $internalAction->isPrivate(),
            sealed: $internalAction->getExternalSealed(),
            silent: $internalAction->isSilent(),
            fallbacks: $internalAction->getExternalFallbacks(),
            retries: $internalAction->getRetries(),
            retryDelay: $internalAction->getRetryDelay(),
            attributes: $internalAction->getAttributes(),
        );
    }

    #[Override]
    public function jsonSerialize(): array
    {
        $require = [];

        foreach ($this->required as $actionId) {
            $require[] = IdFormatter::toString($actionId);
        }

        $sealed = [];

        foreach ($this->sealed as $actionId) {
            $sealed[] = IdFormatter::toString($actionId);
        }

        $fallbacks = [];

        foreach ($this->fallbacks as $actionId) {
            $fallbacks[] = IdFormatter::toString($actionId);
        }

        $onAny = [];

        foreach ($this->onAny as $eventId) {
            $onAny[] = (string) $eventId;
        }

        $onAll = [];

        foreach ($this->onAll as $eventId) {
            $onAll[] = (string) $eventId;
        }

        return [
            'id' => IdFormatter::toString($this->id),
            'description' => $this->description,
            'handler' => $this->handler,
            'onOne' => null !== $this->onOne ? (string) $this->onOne : null,
            'onAny' => $onAny,
            'onAll' => $onAll,
            'require' => $require,
            'argument' => $this->argument,
            'argumentFactory' => $this->argumentFactory,
            'type' => $this->type,
            'immutable' => $this->immutable,
            'lock' => $this->lock,
            'externalAccess' => $this->externalAccess,
            'silent' => $this->silent,
            'retries' => $this->retries,
            'retryDelay' => $this->retryDelay,
            'context' => $this->context,
            'private' => $this->private,
            'sealed' => $sealed,
            'fallbacks' => $fallbacks,
            'rollback' => $this->rollback,
            'attributes' => $this->attributes,
        ];
    }
}
