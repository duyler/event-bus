<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Closure;
use DateInterval;
use Duyler\EventBus\Bus\Action as InternalAction;
use UnitEnum;

final readonly class Action
{
    public function __construct(
        public string|UnitEnum $id,
        public string|Closure $handler,
        public ?string $description = null,

        /** @var array<array-key, string|UnitEnum> */
        public array $required = [],

        /** @var array<array-key, Type> */
        public array $dependsOn = [],

        /** @var array<array-key, string|UnitEnum> */
        public array $listen = [],

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
        public string|null $context = null,

        /** @var class-string|null */
        public string|null $type = null,

        /** @var class-string|null */
        public string|null $typeCollection = null,
        public bool $immutable = true,
        public string|Closure|null $rollback = null,
        public bool $externalAccess = true,
        public bool $repeatable = false,
        public bool $lock = true,
        public bool $private = false,

        /** @var array<array-key, string|UnitEnum> */
        public array $sealed = [],
        public bool $silent = false,

        /** @var array<array-key, string|UnitEnum> */
        public array $alternates = [],
        public int $retries = 0,
        public null|DateInterval $retryDelay = null,

        /** @var array<string|int, mixed> */
        public array $labels = [],
    ) {}

    public static function fromInternal(InternalAction $internalAction): Action
    {
        return new static(
            id: $internalAction->getExternalId(),
            handler: $internalAction->getHandler(),
            description: $internalAction->getDescription(),
            required: $internalAction->getExternalRequired(),
            listen: $internalAction->getExternalListen(),
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
            alternates: $internalAction->getExternalAlternates(),
            retries: $internalAction->getRetries(),
            retryDelay: $internalAction->getRetryDelay(),
            labels: $internalAction->getLabels(),
        );
    }
}
