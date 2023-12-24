<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionRunner;
use Duyler\EventBus\Action\ActionSubstitution;
use Duyler\EventBus\Contract\ActionRunnerInterface;
use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Dto\Config as ConfigDTO;
use Duyler\EventBus\State\StateAction;
use Duyler\EventBus\State\StateMain;

class Config
{
    public readonly array $classMap;
    public readonly array $providers;
    public readonly array $definitions;

    public function __construct(ConfigDTO $config)
    {
        $this->classMap = $this->getBind() + $config->bind;
        $this->providers = $config->providers;
        $this->definitions = $config->definitions;
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
