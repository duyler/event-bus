<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\DI\ContainerInterface;
use Duyler\EventBus\Build\Action;

readonly class Rollback
{
    public function __construct(
        public ContainerInterface $container,
        public Action $action,
        public ?object $argument = null,
        public ?Result $result = null,
    ) {}
}
