<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\DI\ContainerInterface;
use Duyler\EventBus\Bus\Actor;

readonly class Rollback
{
    public function __construct(
        public ContainerInterface $container,
        public Actor $actor,
        public ?object $argument = null,
        public ?Result $result = null,
    ) {}
}
