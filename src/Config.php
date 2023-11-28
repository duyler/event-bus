<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionHandler;
use Duyler\EventBus\Action\ActionSubstitution;
use Duyler\EventBus\Contract\ActionHandlerInterface;
use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Dto\Config as ConfigDTO;
use Duyler\EventBus\State\StateAction;
use Duyler\EventBus\State\StateMain;

class Config
{
    private const string ACTION_CONTAINER_CACHE_DIR = 'action_container';

    public readonly string $actionContainerCacheDir;
    public readonly array $classMap;

    public function __construct(ConfigDTO $config)
    {
        $this->actionContainerCacheDir = $config->defaultCacheDir . self::ACTION_CONTAINER_CACHE_DIR;
        $this->classMap = $this->getBind() + $config->classMap;
    }

    private function getBind(): array
    {
        return [
            ActionHandlerInterface::class => ActionHandler::class,
            StateMainInterface::class => StateMain::class,
            StateActionInterface::class => StateAction::class,
            ActionSubstitutionInterface::class => ActionSubstitution::class,
        ];
    }
}
