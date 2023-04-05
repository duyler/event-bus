<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use DomainException;
use Duyler\EventBus\Contract\HandlerInterface;
use Duyler\EventBus\Contract\RollbackActionInterface;
use Duyler\EventBus\Contract\ValidateCacheHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\SubscribeStorage;
use LogicException;
use OutOfBoundsException;
use function class_exists;
use function class_implements;
use function in_array;
use function md5;
use function serialize;

// TODO валидатор переписать он не рабочий при этом валидатор часть логики
class BusValidator
{
    private SubscribeStorage $subscribeStorage;
    private ?ValidateCacheHandlerInterface $validateCacheHandler = null;
    private ActionStorage $actionStorage;

    public function __construct(
        SubscribeStorage $subscribeStorage,
        ActionStorage $actionStorage
    ) {
        $this->subscribeStorage = $subscribeStorage;
        $this->actionStorage = $actionStorage;
    }

    public function setValidateCacheHandler(ValidateCacheHandlerInterface $validateCacheHandler): static
    {
        $this->validateCacheHandler = $validateCacheHandler;
        return $this;
    }

    public function validate(): void
    {
//        if ($this->validateCacheHandler === null) {
//            $this->runValidation();
//            return;
//        }
//
//        $dataHash = $this->createDataHash();
//
//        if ($this->isValidCache($dataHash) === false) {
//            $this->runValidation();
//            $this->updateCache($dataHash);
//        }
    }

    private function runValidation(): void
    {
        $actions = $this->actionStorage->getAll();

        foreach ($actions as $action) {
            $this->checkRequired($action);
            $this->checkHandler($action->handler);
            $this->checkRollback($action->rollback);
        }

        $allSubscribes = $this->subscribeStorage->getAll();

        foreach ($allSubscribes as $subjectActionFullName => $subscribes) {
            $this->checkSubscribes($subjectActionFullName, $subscribes);
        }
    }

    private function checkHandler(string $handler): void
    {
        if (class_exists($handler) === false) {
            throw new DomainException('Class ' . $handler . ' not found');
        }

        $interfaces = class_implements($handler);

        if (empty($interfaces) or in_array(HandlerInterface::class, $interfaces) === false) {
            throw new DomainException('Handler class must be implements ' . HandlerInterface::class);
        }
    }

    private function checkRequired(Action $action): void
    {
        foreach ($action->required as $subject) {
            if ($this->actionStorage->isExists($subject) === false) {
                throw new OutOfBoundsException('Required action ' . $subject . ' not registered in the bus');
            }

            $requiredAction = $this->actionStorage->get($subject);

            if (in_array($action->id, $requiredAction->required->getArrayCopy())) {
                throw new LogicException('Action ' . $action->id . ' require action');
            }
        }
    }

    private function checkRollback(string $rollbackHandler): void
    {
        if (empty($rollbackHandler)) {
            return;
        }

        if (class_exists($rollbackHandler) === false) {
            throw new DomainException('Class ' . $rollbackHandler . ' not found');
        }

        $interfaces = class_implements($rollbackHandler);

        if (empty($interfaces) or in_array(RollbackActionInterface::class, $interfaces) === false) {
            throw new DomainException('Rollback handler class must be implements ' . RollbackActionInterface::class);
        }
    }

    private function checkSubscribes(string $subjectActionFullName, array $subscribes): void
    {
        foreach ($subscribes as $subscribe) {

            if ($this->actionStorage->isExists($subjectActionFullName) === false) {
                throw new OutOfBoundsException('Subscribed action ' . $subjectActionFullName . ' not registered in the bus');
            }

            if ($this->actionStorage->isExists($subscribe->actionId) === false) {
                throw new OutOfBoundsException('Action ' . $subscribe->actionId . ' not registered in the bus');
            }
        }
    }

    private function createDataHash(): string
    {
        return md5(serialize($this->subscribeStorage)) . md5(serialize($this->actionStorage));
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
        if ($this->validateCacheHandler !== null) {
            $this->validateCacheHandler->writeHash($dataHash);
        }
    }
}
