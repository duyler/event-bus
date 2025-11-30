<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Closure;
use DateInterval;
use Duyler\EventBus\Bus\Action as InternalAction;
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
        public array $alternates = [],
        public int $retries = 0,
        public ?DateInterval $retryDelay = null,

        /** @var array<string|int, mixed> */
        public array $attributes = [],
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

        $listen = [];

        foreach ($this->listen as $eventId) {
            $listen[] = IdFormatter::toString($eventId);
        }

        $sealed = [];

        foreach ($this->sealed as $actionId) {
            $sealed[] = IdFormatter::toString($actionId);
        }

        $alternates = [];

        foreach ($this->alternates as $actionId) {
            $alternates[] = IdFormatter::toString($actionId);
        }

        return [
            'id' => IdFormatter::toString($this->id),
            'description' => $this->description,
            'handler' => $this->handler,
            'require' => $require,
            'listen' => $listen,
            'argument' => $this->argument,
            'argumentFactory' => $this->argumentFactory,
            'type' => $this->type,
            'immutable' => $this->immutable,
            'dependsOn' => $this->dependsOn,
            'lock' => $this->lock,
            'externalAccess' => $this->externalAccess,
            'silent' => $this->silent,
            'retries' => $this->retries,
            'retryDelay' => $this->retryDelay,
            'context' => $this->context,
            'private' => $this->private,
            'sealed' => $sealed,
            'alternates' => $alternates,
            'rollback' => $this->rollback,
            'attributes' => $this->attributes,
        ];
    }
}
