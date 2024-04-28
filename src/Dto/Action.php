<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Dto;

use UnitEnum;
use Closure;
use Duyler\ActionBus\Formatter\IdFormatter;
use RecursiveArrayIterator;

readonly class Action
{
    public string $id;
    /** @var RecursiveArrayIterator<array-key, string> */
    public RecursiveArrayIterator $required;
    public ?string $triggeredOn;
    /** @var string[] */
    public array $sealed;
    /** @var string[] */
    public array $alternates;

    public function __construct(
        string|UnitEnum $id,
        public string|Closure $handler,
        /** @param array<array-key, string|UnitEnum> $required  */
        array $required = [],
        null|string|UnitEnum $triggeredOn = null,
        /** @var array<string, string> */
        public array $bind = [],
        /** @var array<string, string> */
        public array $providers = [],
        public ?string $argument = null,
        public string|Closure|null $argumentFactory = null,
        public ?string $contract = null,
        public string|Closure|null $rollback = null,
        public bool $externalAccess = true,
        public bool $repeatable = false,
        public bool $lock = true,
        public bool $private = false,
        /** @param array<array-key, string|UnitEnum> $sealed */
        array $sealed = [],
        public bool $silent = false,
        /** @param array<array-key, string|UnitEnum> $alternates */
        array $alternates = [],
        public int $retries = 0,
        /** @var array<string|int, mixed> */
        public array $labels = [],
    ) {
        $this->id = IdFormatter::format($id);

        $this->required = new RecursiveArrayIterator();

        /** @var string|UnitEnum $actionId */
        foreach ($required as $actionId) {
            $this->required->append(IdFormatter::format($actionId));
        }

        $alternatesActions = [];

        /** @var string|UnitEnum $actionId */
        foreach ($alternates as $actionId) {
            $alternatesActions[] = IdFormatter::format($actionId);
        }

        $this->alternates = $alternatesActions;

        $allowActions = [];

        /** @var string|UnitEnum $actionId */
        foreach ($sealed as $actionId) {
            $allowActions[] = IdFormatter::format($actionId);
        }

        $this->sealed = $allowActions;

        $this->triggeredOn = $triggeredOn === null ? null : IdFormatter::format($triggeredOn);
    }
}
