<?php 

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\ValidateCacheHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscribe;
use Throwable;

class Bus
{
    public function __construct(
        private readonly Dispatcher   $dispatcher,
        private readonly BusValidator $busValidator,
        private readonly DoWhile      $doWhile,
        private readonly Rollback     $rollback,
        private readonly Storage      $storage,
    ) {
    }

    public function addAction(Action $action): static
    {
        $this->storage->action()->save($action);
        return $this;
    }

    public function addSubscribe(Subscribe $subscribe): static
    {
        $this->storage->subscribe()->save($subscribe);
        return $this;
    }

    public function run(string $startAction): void
    {
        $this->dispatcher->prepareStartedTask($startAction);

         try {
             $this->doWhile->run();
         } catch (Throwable $th) {
             $this->rollback->run();
             throw $th;
         }
    }

    public function setValidateCacheHandler(ValidateCacheHandlerInterface $validateCacheHandler): static
    {
        //TODO validation
        $this->busValidator->setValidateCacheHandler($validateCacheHandler);
        return $this;
    }

    public function actionIsExists(string $actionFullName): bool
    {
        return $this->storage->action()->isExists($actionFullName);
    }

    public function getResult(string $actionFullName): ?Result
    {
        return $this->storage->task()->getResult($actionFullName);
    }
}
