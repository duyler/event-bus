<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Dto;

use Duyler\DependencyInjection\ContainerInterface;
use Duyler\ActionBus\Build\Action;

readonly class Rollback
{
    public function __construct(
        public ContainerInterface $container,
        public Action $action,
        public ?object $argument = null,
        public ?Result $result = null,
    ) {}
}
