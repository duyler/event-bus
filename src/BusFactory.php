<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DependencyInjection\ContainerBuilder;

class BusFactory
{
    public static function create(): Bus
    {
        $container = ContainerBuilder::build();
        return $container->make(Bus::class);
    }
}
