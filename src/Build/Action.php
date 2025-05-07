<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Closure;
use DateInterval;
use Duyler\EventBus\Formatter\IdFormatter;
use InvalidArgumentException;
use RecursiveArrayIterator;
use ReflectionClass;
use UnitEnum;

final readonly class Action
{
    public string $id;
    /** @var RecursiveArrayIterator<array-key, string> */
    public RecursiveArrayIterator $required;
    /** @var string[] */
    public array $listen;
    /** @var string[] */
    public array $sealed;
    /** @var string[] */
    public array $alternates;

    public function __construct(
        string|UnitEnum $id,
        public string|Closure $handler,
        /** @var array<array-key, string|UnitEnum> */
        array $required = [],
        /** @var array<array-key, string|UnitEnum> */
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
        array $sealed = [],
        public bool $silent = false,
        /** @var array<array-key, string|UnitEnum> */
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

        $this->id = IdFormatter::toString($id);

        $this->required = new RecursiveArrayIterator();

        /** @var string|UnitEnum $actionId */
        foreach ($required as $actionId) {
            $this->required->append(IdFormatter::toString($actionId));
        }

        $alternatesActions = [];

        /** @var string|UnitEnum $actionId */
        foreach ($alternates as $actionId) {
            $alternatesActions[] = IdFormatter::toString($actionId);
        }

        $this->alternates = $alternatesActions;

        $allowActions = [];

        /** @var string|UnitEnum $actionId */
        foreach ($sealed as $actionId) {
            $allowActions[] = IdFormatter::toString($actionId);
        }

        $this->sealed = $allowActions;

        $listenEvents = [];

        /** @var string|UnitEnum $eventId */
        foreach ($listen as $eventId) {
            $listenEvents[] = IdFormatter::toString($eventId);
        }

        $this->listen = $listenEvents;
    }
}
