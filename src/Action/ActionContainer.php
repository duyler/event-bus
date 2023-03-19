<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\DependencyInjection\Compiler;
use Duyler\DependencyInjection\Container;
use Duyler\DependencyInjection\DependencyMapper;
use Duyler\DependencyInjection\ReflectionStorage;

class ActionContainer extends Container
{
    public function __construct(public readonly string $actionId)
    {
        $reflectionStorage = new ReflectionStorage();
        parent::__construct(new Compiler(), new DependencyMapper($reflectionStorage));
    }

    public static function build(string $actionId): self
    {
        return new self($actionId);
    }
}
