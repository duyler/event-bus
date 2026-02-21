<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Closure;
use DateInterval;
use Duyler\EventBus\Build\Action as ExternalAction;
use Duyler\EventBus\Build\Id;
use Duyler\EventBus\Build\Type;
use Duyler\EventBus\Enum\SubscriptionType;
use Duyler\EventBus\Formatter\IdFormatter;
use InvalidArgumentException;
use RecursiveArrayIterator;
use ReflectionClass;
use UnitEnum;

final class Action
{
    public const string COLLECTION_PREFIX = 'Collection@';

    private readonly ?string $typeId;

    /** @var RecursiveArrayIterator<array-key, string> */
    private readonly RecursiveArrayIterator $required;

    /** @var array<array-key, string> */
    private readonly array $dependsOn;

    /** @var string[] */
    private readonly array $sealed;

    /** @var array<array-key, string|UnitEnum> */
    private readonly array $externalSealed;

    /** @var string[] */
    private readonly array $alternates;

    /** @var array<array-key, string|UnitEnum> */
    private readonly array $externalAlternates;

    private ?string $onOne = null;

    /** @var string[] */
    private array $onAny = [];

    /** @var string[] */
    private array $onAll = [];

    /**
     * @param array<array-key, string|UnitEnum> $externalRequired
     * @param array<array-key, Type> $dependsOn
     * @param array<array-key, string|UnitEnum> $sealed
     * @param array<array-key, string|UnitEnum> $alternates
     */
    public function __construct(
        private readonly string $id,
        private readonly string|UnitEnum $externalId,
        private readonly string|Closure $handler,
        private readonly ?string $description = null,

        /** @var array<array-key, string|UnitEnum> */
        private readonly array $externalRequired = [],
        array $dependsOn = [],

        /** @var array<string, string> */
        private readonly array $bind = [],

        /** @var array<string, string> */
        private readonly array $providers = [],

        /** @var array<string, array<string, mixed>> */
        private readonly array $definitions = [],
        private readonly ?string $argument = null,

        /** @var class-string|Closure|null */
        private readonly string|Closure|null $argumentFactory = null,

        /** @var class-string|null */
        private readonly ?string $context = null,

        /** @var class-string|null */
        private readonly ?string $type = null,

        /** @var class-string|null */
        private readonly ?string $typeCollection = null,
        private readonly bool $immutable = true,
        private readonly string|Closure|null $rollback = null,
        private readonly bool $externalAccess = true,
        private readonly bool $repeatable = false,
        private readonly bool $lock = true,
        private readonly bool $private = false,
        array $sealed = [],
        private readonly bool $silent = false,
        array $alternates = [],
        private readonly int $retries = 0,
        private readonly ?DateInterval $retryDelay = null,

        /** @var array<string|int, mixed> */
        private readonly array $attributes = [],
        string|Id|null $onOne = null,
        array $onAny = [],
        array $onAll = [],
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

        if (null !== $this->typeCollection) {
            $this->typeId = self::COLLECTION_PREFIX . $this->type;
        } else {
            $this->typeId = $this->type;
        }

        $this->required = new RecursiveArrayIterator();

        /** @var string|UnitEnum $actionId */
        foreach ($this->externalRequired as $actionId) {
            $this->required->append(IdFormatter::toString($actionId));
        }

        $dependsOnIds = [];

        foreach ($dependsOn as $type) {
            $typeId = '';

            if ($type->typeCollection) {
                $typeId = self::COLLECTION_PREFIX;
            }

            $dependsOnIds[] = $typeId . $type->type;
        }

        $this->dependsOn = $dependsOnIds;

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

        if (null !== $onOne) {
            $this->onOne = (string) $onOne;
        }

        /** @var string|Id $eventId */
        foreach ($onAny as $eventId) {
            $this->onAny[] = (string) $eventId;
        }

        /** @var string|Id $eventId */
        foreach ($onAll as $eventId) {
            $this->onAll[] = (string) $eventId;
        }
    }

    public static function fromExternal(ExternalAction $externalAction): Action
    {
        return new static(
            id: IdFormatter::toString($externalAction->id),
            externalId: $externalAction->id,
            handler: $externalAction->handler,
            description: $externalAction->description,
            externalRequired: $externalAction->required,
            dependsOn: $externalAction->dependsOn,
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
            attributes: $externalAction->attributes,
            onOne: $externalAction->onOne,
            onAny: $externalAction->onAny,
            onAll: $externalAction->onAll,
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
     * @return array<array-key, string>
     */
    public function getDependsOn(): array
    {
        return $this->dependsOn;
    }

    public function getTypeId(): ?string
    {
        return $this->typeId;
    }

    public function getOnOne(): ?string
    {
        return $this->onOne;
    }

    /**
     * @return string[]
     */
    public function getOnAny(): array
    {
        return $this->onAny;
    }

    /**
     * @return string[]
     */
    public function getOnAll(): array
    {
        return $this->onAll;
    }

    public function getSubscriptionType(): SubscriptionType
    {
        if (null !== $this->onOne) {
            return SubscriptionType::One;
        }
        if (count($this->onAny) > 0) {
            return SubscriptionType::Any;
        }
        if (count($this->onAll) > 0) {
            return SubscriptionType::All;
        }
        return SubscriptionType::None;
    }

    /**
     * @return string[]
     */
    public function getSubscriptionEvents(): array
    {
        return match ($this->getSubscriptionType()) {
            SubscriptionType::One => (function (): array {
                assert(null !== $this->onOne);
                return [$this->onOne];
            })(),
            SubscriptionType::Any => $this->onAny,
            SubscriptionType::All => $this->onAll,
            SubscriptionType::None => [],
        };
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
    public function getContext(): ?string
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

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
