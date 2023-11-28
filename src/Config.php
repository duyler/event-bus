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
    private const string ACTION_CONTAINER_CACHE_DIR = 'action_container';

    public readonly bool $enableCache;
    public readonly string $fileCacheDirPath;
    public readonly array $classMap;

    public function __construct(ConfigDTO $config)
    {
        $this->enableCache = $config->enableCache;
        $this->fileCacheDirPath = $config->fileCacheDirPath . self::ACTION_CONTAINER_CACHE_DIR;
        $this->classMap = $this->getBind() + $config->classMap;
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
