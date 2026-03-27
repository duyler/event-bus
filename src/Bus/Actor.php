<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Closure;
use DateInterval;
use Duyler\EventBus\Build\Actor as ExternalActor;
use Duyler\EventBus\Build\Id;
use Duyler\EventBus\Enum\SubscriptionType;
use Duyler\EventBus\Formatter\IdFormatter;
use InvalidArgumentException;
use RecursiveArrayIterator;
use ReflectionClass;
use UnitEnum;

final class Actor
{
    public const string COLLECTION_PREFIX = 'Collection@';

    private readonly ?string $typeId;

    /** @var RecursiveArrayIterator<array-key, string> */
    private readonly RecursiveArrayIterator $required;

    /** @var string[] */
    private readonly array $sealed;

    /** @var array<array-key, string|UnitEnum> */
    private readonly array $externalSealed;

    /** @var string[] */
    private readonly array $fallbacks;

    /** @var array<array-key, string|UnitEnum> */
    private readonly array $externalFallbacks;

    private ?string $onOne = null;

    /** @var string[] */
    private array $onAny = [];

    /** @var string[] */
    private array $onAll = [];

    /**
     * @param array<array-key, string|UnitEnum> $externalRequired
     * @param array<array-key, string|UnitEnum> $sealed
     * @param array<array-key, string|UnitEnum> $fallbacks
     */
    public function __construct(
        private readonly string $id,
        private readonly string|UnitEnum $externalId,
        private readonly string|Closure $handler,
        private readonly ?string $description = null,

        /** @var array<array-key, string|UnitEnum> */
        private readonly array $externalRequired = [],

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
        array $fallbacks = [],
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

        /** @var string|UnitEnum $actorId */
        foreach ($this->externalRequired as $actorId) {
            $this->required->append(IdFormatter::toString($actorId));
        }

        $fallbackActors = [];

        /** @var string|UnitEnum $actorId */
        foreach ($fallbacks as $actorId) {
            $fallbackActors[] = IdFormatter::toString($actorId);
        }

        $this->fallbacks = $fallbackActors;
        $this->externalFallbacks = $fallbacks;

        $allowActors = [];

        /** @var string|UnitEnum $actorId */
        foreach ($sealed as $actorId) {
            $allowActors[] = IdFormatter::toString($actorId);
        }

        $this->sealed = $allowActors;
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

    public static function fromExternal(ExternalActor $externalActor): Actor
    {
        return new static(
            id: IdFormatter::toString($externalActor->id),
            externalId: $externalActor->id,
            handler: $externalActor->handler,
            description: $externalActor->description,
            externalRequired: $externalActor->required,
            bind: $externalActor->bind,
            providers: $externalActor->providers,
            definitions: $externalActor->definitions,
            argument: $externalActor->argument,
            argumentFactory: $externalActor->argumentFactory,
            context: $externalActor->context,
            type: $externalActor->type,
            typeCollection: $externalActor->typeCollection,
            immutable: $externalActor->immutable,
            rollback: $externalActor->rollback,
            externalAccess: $externalActor->externalAccess,
            repeatable: $externalActor->repeatable,
            lock: $externalActor->lock,
            private: $externalActor->private,
            sealed: $externalActor->sealed,
            silent: $externalActor->silent,
            fallbacks: $externalActor->fallbacks,
            retries: $externalActor->retries,
            retryDelay: $externalActor->retryDelay,
            attributes: $externalActor->attributes,
            onOne: $externalActor->onOne,
            onAny: $externalActor->onAny,
            onAll: $externalActor->onAll,
        );
    }

    /**
     * @return RecursiveArrayIterator<array-key, string>
     */
    public function getRequired(): RecursiveArrayIterator
    {
        return $this->required;
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
    public function getFallbacks(): array
    {
        return $this->fallbacks;
    }

    /**
     * @return array<array-key, string|UnitEnum>
     */
    public function getExternalFallbacks(): array
    {
        return $this->externalFallbacks;
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
