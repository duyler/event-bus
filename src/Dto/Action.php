<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Closure;
use RecursiveArrayIterator;

readonly class Action
{
    public RecursiveArrayIterator $required;

    public function __construct(
        public string           $id,
        public string | Closure $handler,
        array                   $required = [],
        public array            $classMap = [],
        public array            $providers = [],
        public string | Closure $rollback = '',
        public array            $arguments = [],
        public bool             $externalAccess = false,
        public bool             $repeatable = false,
        public ?string          $contract = null,
    ) {
        $this->required = new RecursiveArrayIterator($required);
    }
}
