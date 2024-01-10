<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionRunner;
use Duyler\EventBus\Action\ActionSubstitution;
use Duyler\EventBus\Contract\ActionRunnerInterface;
use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\State\StateAction;
use Duyler\EventBus\State\StateMain;

class BusConfig
{
    public readonly array $bind;

    public function __construct(
        array $bind = [],
        public readonly array $providers = [],
        public readonly array $definitions = [],
        public readonly bool $enableTriggers = true,
    ) {
        $this->bind = $this->getBind() + $bind;
    }

    private function getBind(): array
    {
        return [
            ActionRunnerInterface::class => ActionRunner::class,
            StateMainInterface::class => StateMain::class,
            StateActionInterface::class => StateAction::class,
            ActionSubstitutionInterface::class => ActionSubstitution::class,
        ];
    }
}
