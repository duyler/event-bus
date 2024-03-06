<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Closure;
use RecursiveArrayIterator;

readonly class Action
{
    /** @var RecursiveArrayIterator<array-key, string> */
    public RecursiveArrayIterator $required;

    /** @param array<array-key, string> $required  */
    public function __construct(
        public string $id,
        public string | Closure $handler,
        array $required = [],
        public ?string $triggeredOn = null,
        /** @var array<string, string> */
        public array $bind = [],
        /** @var array<string, string> */
        public array $providers = [],
        public ?string $argument = null,
        public null | string | Closure $argumentFactory = null,
        public ?string $contract = null,
        public null | string | Closure $rollback = null,
        public bool $externalAccess = true,
        public bool $repeatable = false,
        public bool $lock = true,
        public bool $private = false,
        /** @var string[] */
        public array $sealed = [],
        public bool $silent = false,
        /** @var string[] */
        public array $alternates = [],
        public int $retries = 0,
    ) {
        $this->required = new RecursiveArrayIterator($required);
    }
}
