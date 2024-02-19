<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Closure;
use RecursiveArrayIterator;

readonly class Action
{
    public RecursiveArrayIterator $required;

    public function __construct(
        public string $id,
        public string | Closure $handler,
        array $required = [],
        public array $bind = [],
        public array $providers = [],
        public string $argument = '',
        public ?string $argumentFactory = null,
        public ?string $contract = null,
        public string | Closure $rollback = '',
        public bool $externalAccess = true,
        public bool $repeatable = false,
        public bool $continueIfFail = true,
        public bool $private = false,
        /** @var string[] */
        public array $sealed = [],
        public bool $silent = false,
    ) {
        $this->required = new RecursiveArrayIterator($required);
    }
}
