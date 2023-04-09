<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\RollbackActionInterface;
use Duyler\EventBus\Contract\ValidateCacheHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscribe;
use Duyler\EventBus\Exception\CircularCallActionException;
use InvalidArgumentException;
use LogicException;
use function in_array;
use function count;
use function end;
use function md5;
use function class_exists;
use function class_implements;
use function serialize;

class Validator
{
    private ?ValidateCacheHandlerInterface $validateCacheHandler = null;

    private array $mainEventLog = [];
    private array $repeatedEventLog = [];

    public function __construct(private readonly Storage $storage)
    {
    }

    public function setValidateCacheHandler(ValidateCacheHandlerInterface $validateCacheHandler): static
    {
        $this->validateCacheHandler = $validateCacheHandler;
        return $this;
    }

    public function validate(): void
    {
        if ($this->validateCacheHandler === null) {
            $this->runValidation();
            return;
        }

        $dataHash = $this->createDataHash();

        if ($this->isValidCache($dataHash) === false) {
            $this->runValidation();
            $this->updateCache($dataHash);
        }
    }

    private function runValidation(): void
    {
        $this->validateActions();
        $this->validateSubscribes();
    }

    private function createDataHash(): string
    {
        return md5(serialize($this->storage));
    }

    private function isValidCache(string $dataHash): bool
    {
        if ($this->validateCacheHandler === null) {
            return false;
        }

        $hash = $this->validateCacheHandler->readHash();

        return $hash === $dataHash;
    }

    private function updateCache(string $dataHash): void
    {
        $this->validateCacheHandler?->writeHash($dataHash);
    }

    public function validateActions(): void
    {
        foreach ($this->storage->action()->getAll() as $action) {
            $this->validateAction($action);
        }
    }

    public function validateAction(Action $action): void
    {
        foreach ($action->required as $subject) {
            if ($this->storage->action()->isExists($subject) === false) {
                throw new InvalidArgumentException(
                    'Required action ' . $subject . ' not registered in the bus'
                );
            }

            $requiredAction = $this->storage->action()->get($subject);

            if (in_array($action->id, $requiredAction->required->getArrayCopy())) {
                throw new LogicException('Action ' . $action->id . ' require action');
            }
        }

        $this->validateHandler($action->handler);
        $this->validateRollback($action->rollback);
    }

    private function validateHandler(callable|string $handler): void
    {
        if (is_string($handler) && class_exists($handler) === false && interface_exists($handler) === false) {
            throw new InvalidArgumentException('Class ' . $handler . ' not found');
        }
    }

    private function validateRollback(callable|string $rollbackHandler): void
    {
        if (empty($rollbackHandler) || is_callable($rollbackHandler)) {
            return;
        }

        if (class_exists($rollbackHandler) === false) {
            throw new InvalidArgumentException('Class ' . $rollbackHandler . ' not found');
        }

        $interfaces = class_implements($rollbackHandler);

        if (empty($interfaces) or in_array(RollbackActionInterface::class, $interfaces) === false) {
            throw new InvalidArgumentException(
                'Rollback handler class must be implements ' . RollbackActionInterface::class
            );
        }
    }

    public function validateSubscribes(): void
    {
        /** @var Subscribe $subscribe */
        foreach ($this->storage->subscribe()->getAll() as $subscribe) {
            $this->validateSubscribe($subscribe);
        }
    }

    public function validateSubscribe(Subscribe $subscribe): void
    {
        if ($this->storage->action()->isExists($subscribe->actionId) === false) {
            throw new InvalidArgumentException(
                'Action ' . $subscribe->actionId . ' not registered in the bus'
            );
        }

        if ($this->storage->action()->isExists($subscribe->subjectId) === false) {
            throw new InvalidArgumentException(
                'Subscribed action ' . $subscribe->subjectId . ' not registered in the bus'
            );
        }
    }


    public function checkCyclicActionCalls(Task $task): void
    {
        if ($this->storage->task()->isExists($task->action->id)) {

            $actionId = $task->action->id . '.' . $task->result->status->value;

            if (in_array($actionId, $this->mainEventLog)) {
                $this->repeatedEventLog[] = $actionId;
            } else {
                $this->mainEventLog[] = $actionId;
            }

            if (count($this->mainEventLog) === count($this->repeatedEventLog)) {
                throw new CircularCallActionException(
                    $task->action->id,
                    $task->subscribe->subjectId ?? end($this->mainEventLog)
                );
            }
        }
    }
}
