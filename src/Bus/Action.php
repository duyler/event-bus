<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Closure;
use DateInterval;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Build\Action as ExternalAction;
use InvalidArgumentException;
use RecursiveArrayIterator;
use ReflectionClass;
use UnitEnum;

final readonly class Action
{
    /** @var RecursiveArrayIterator<array-key, string> */
    public RecursiveArrayIterator $required;

    /** @var string[] */
    public array $listen;

    /** @var array<array-key, string|UnitEnum> */
    public array $externalListen;

    /** @var string[] */
    public array $sealed;

    /** @var array<array-key, string|UnitEnum> */
    public array $externalSealed;

    /** @var string[] */
    public array $alternates;

    /** @var array<array-key, string|UnitEnum> */
    public array $externalAlternates;

    /**
     * @param array<array-key, string|UnitEnum> $externalRequired
     * @param array<array-key, string|UnitEnum> $listen
     * @param array<array-key, string|UnitEnum> $sealed
     * @param array<array-key, string|UnitEnum> $alternates
     */
    public function __construct(
        public string $id,
        public string|UnitEnum $externalId,
        public string|Closure $handler,
        public array $externalRequired = [],
        array $listen = [],

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
        public string|Closure|null $context = null,

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
        array $sealed = [],
        public bool $silent = false,
        array $alternates = [],
        public int $retries = 0,
        public null|DateInterval $retryDelay = null,

        /** @var array<string|int, mixed> */
        public array $labels = [],
    ) {
        if ($this->immutable) {
            if (null !== $this->type) {
                if (interface_exists($this->type)) {
                    throw new InvalidArgumentException('Type of ' . $this->type . ' it should not be an interface');
                }

                /** @var class-string $type */
                $reflectionContract = new ReflectionClass($type);
                if (false === $reflectionContract->isReadOnly()) {
                    throw new InvalidArgumentException('Type ' . $this->type . ' must be read only class');
                }
            }
        }

        if (null === $this->type && null !== $this->typeCollection) {
            throw new InvalidArgumentException('Type not set for collection ' . $this->typeCollection);
        }

        $this->required = new RecursiveArrayIterator();

        /** @var string|UnitEnum $actionId */
        foreach ($this->externalRequired as $actionId) {
            $this->required->append(IdFormatter::toString($actionId));
        }

        $alternatesActions = [];

        /** @var string|UnitEnum $actionId */
        foreach ($alternates as $actionId) {
            $alternatesActions[] = IdFormatter::toString($actionId);
        }

        $this->alternates = $alternatesActions;
        $this->externalAlternates = $alternates;

        $allowActions = [];

        /** @var string|UnitEnum $actionId */
        foreach ($sealed as $actionId) {
            $allowActions[] = IdFormatter::toString($actionId);
        }

        $this->sealed = $allowActions;
        $this->externalSealed = $sealed;

        $listenEvents = [];

        /** @var string|UnitEnum $eventId */
        foreach ($listen as $eventId) {
            $listenEvents[] = IdFormatter::toString($eventId);
        }

        $this->listen = $listenEvents;
        $this->externalListen = $listen;
    }

    public static function fromExternal(ExternalAction $externalAction): Action
    {
        return new static(
            id: IdFormatter::toString($externalAction->id),
            externalId: $externalAction->id,
            handler: $externalAction->handler,
            externalRequired: $externalAction->required,
            listen: $externalAction->listen,
            bind: $externalAction->bind,
            providers: $externalAction->providers,
            definitions: $externalAction->definitions,
            argument: $externalAction->argument,
            argumentFactory: $externalAction->argumentFactory,
            context: $externalAction->context,
            type: $externalAction->type,
            typeCollection: $externalAction->typeCollection,
            immutable: $externalAction->immutable,
            rollback: $externalAction->rollback,
            externalAccess: $externalAction->externalAccess,
            repeatable: $externalAction->repeatable,
            lock: $externalAction->lock,
            private: $externalAction->private,
            sealed: $externalAction->sealed,
            silent: $externalAction->silent,
            alternates: $externalAction->alternates,
            retries: $externalAction->retries,
            retryDelay: $externalAction->retryDelay,
            labels: $externalAction->labels,
        );
    }
}
