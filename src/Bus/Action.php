<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Closure;
use DateInterval;
use Duyler\EventBus\Build\Action as ExternalAction;
use Duyler\EventBus\Formatter\IdFormatter;
use InvalidArgumentException;
use RecursiveArrayIterator;
use ReflectionClass;
use UnitEnum;

final readonly class Action
{
    /** @var RecursiveArrayIterator<array-key, string> */
    private RecursiveArrayIterator $required;

    /** @var string[] */
    private array $listen;

    /** @var array<array-key, string|UnitEnum> */
    private array $externalListen;

    /** @var string[] */
    private array $sealed;

    /** @var array<array-key, string|UnitEnum> */
    private array $externalSealed;

    /** @var string[] */
    private array $alternates;

    /** @var array<array-key, string|UnitEnum> */
    private array $externalAlternates;

    /**
     * @param array<array-key, string|UnitEnum> $externalRequired
     * @param array<array-key, string|UnitEnum> $listen
     * @param array<array-key, string|UnitEnum> $sealed
     * @param array<array-key, string|UnitEnum> $alternates
     */
    public function __construct(
        private string $id,
        private string|UnitEnum $externalId,
        private string|Closure $handler,

        /** @var array<array-key, string|UnitEnum> */
        private array $externalRequired = [],
        array $listen = [],

        /** @var array<string, string> */
        private array $bind = [],

        /** @var array<string, string> */
        private array $providers = [],

        /** @var array<string, array<string, mixed>> */
        private array $definitions = [],
        private ?string $argument = null,

        /** @var class-string|Closure|null */
        private string|Closure|null $argumentFactory = null,

        /** @var class-string|null */
        private string|null $context = null,

        /** @var class-string|null */
        private string|null $type = null,

        /** @var class-string|null */
        private string|null $typeCollection = null,
        private bool $immutable = true,
        private string|Closure|null $rollback = null,
        private bool $externalAccess = true,
        private bool $repeatable = false,
        private bool $lock = true,
        private bool $private = false,
        array $sealed = [],
        private bool $silent = false,
        array $alternates = [],
        private int $retries = 0,
        private null|DateInterval $retryDelay = null,

        /** @var array<string|int, mixed> */
        private array $labels = [],
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

    /**
     * @return RecursiveArrayIterator<array-key, string>
     */
    public function getRequired(): RecursiveArrayIterator
    {
        return $this->required;
    }

    /**
     * @return string[]
     */
    public function getListen(): array
    {
        return $this->listen;
    }

    /**
     * @return array<array-key, string|UnitEnum>
     */
    public function getExternalListen(): array
    {
        return $this->externalListen;
    }

    /**
     * @return string[]
     */
    public function getSealed(): array
    {
        return $this->sealed;
    }

    /**
     * @return array<array-key, string|UnitEnum>
     */
    public function getExternalSealed(): array
    {
        return $this->externalSealed;
    }

    /**
     * @return string[]
     */
    public function getAlternates(): array
    {
        return $this->alternates;
    }

    /**
     * @return array<array-key, string|UnitEnum>
     */
    public function getExternalAlternates(): array
    {
        return $this->externalAlternates;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getExternalId(): UnitEnum|string
    {
        return $this->externalId;
    }

    public function getHandler(): Closure|string
    {
        return $this->handler;
    }

    /**
     * @return array<array-key, string|UnitEnum>
     */
    public function getExternalRequired(): array
    {
        return $this->externalRequired;
    }

    /**
     * @return array<string, string>
     */
    public function getBind(): array
    {
        return $this->bind;
    }

    /**
     * @return array<string, string>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function getArgument(): ?string
    {
        return $this->argument;
    }

    /**
     * @return class-string|Closure|null
     */
    public function getArgumentFactory(): Closure|string|null
    {
        return $this->argumentFactory;
    }

    /**
     * @return class-string|null
     */
    public function getContext(): string|null
    {
        return $this->context;
    }

    /**
     * @return class-string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return class-string|null
     */
    public function getTypeCollection(): ?string
    {
        return $this->typeCollection;
    }

    public function isImmutable(): bool
    {
        return $this->immutable;
    }

    public function getRollback(): Closure|string|null
    {
        return $this->rollback;
    }

    public function isExternalAccess(): bool
    {
        return $this->externalAccess;
    }

    public function isRepeatable(): bool
    {
        return $this->repeatable;
    }

    public function isLock(): bool
    {
        return $this->lock;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function isSilent(): bool
    {
        return $this->silent;
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    public function getRetryDelay(): ?DateInterval
    {
        return $this->retryDelay;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }
}
