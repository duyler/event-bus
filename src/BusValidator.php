<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Action;
use Jine\EventBus\Contract\HandlerInterface;
use Jine\EventBus\Contract\RollbackInterface;
use Jine\EventBus\Contract\ValidateCacheHandlerInterface;
use OutOfBoundsException;
use DomainException;
use LogicException;

use function serialize;
use function md5;
use function class_exists;
use function in_array;
use function explode;

class BusValidator
{
    private ServiceStorage $serviceStorage;
    private SubscribeStorage $subscribeStorage;
    private ?ValidateCacheHandlerInterface $validateCacheHandler = null;
    private ActionStorage $actionStorage;

    public function __construct(
        ServiceStorage $serviceStorage,
        SubscribeStorage $subscribeStorage,
        ActionStorage $actionStorage
    ) {
        $this->serviceStorage = $serviceStorage;
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
        $actions = $this->actionStorage->getAll();

        foreach ($actions as $action) {
            $this->checkRequired($action);
            $this->checkHandler($action->handler);
            $this->checkRollback($action->rollback);
        }

        $allSubscribes = $this->subscribeStorage->getAll();

        foreach ($allSubscribes as $subscribes) {
            $this->checkSubscribes($subscribes);
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

            if (in_array($action->serviceId . '.' . $action->name, $requiredAction->required)) {
                throw new LogicException('Action ' . $action->serviceId . '.' . $action->name . ' require action');
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

        if (empty($interfaces) or in_array(RollbackInterface::class, $interfaces) === false) {
            throw new DomainException('Rollback handler class must be implements ' . RollbackInterface::class);
        }
    }

    private function checkSubscribes(array $subscribes): void
    {
        foreach ($subscribes as $subscribe) {
            $segments = explode('.', $subscribe->subject);

            $actionFullName = $segments[0] . '.' . $segments[1];

            if ($this->actionStorage->isExists($actionFullName) === false) {
                throw new OutOfBoundsException('Subscribed action ' . $actionFullName . ' not registered in the bus');
            }

            if ($this->actionStorage->isExists($subscribe->actionFullName) === false) {
                throw new OutOfBoundsException('Action ' . $subscribe->actionFullName . ' not registered in the bus');
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
