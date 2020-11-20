<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Action;
use Jine\EventBus\Contract\HandlerInterface;
use Jine\EventBus\Contract\RollbackInterface;
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
    private ConfigProvider $configProvider;
    private ActionStorage $actionStorage;

    private const FILE_NAME = 'bus_validation';

    public function __construct(
        ServiceStorage $serviceStorage,
        SubscribeStorage $subscribeStorage,
        ConfigProvider $configProvider,
        ActionStorage $actionStorage
    ) {
        $this->serviceStorage = $serviceStorage;
        $this->subscribeStorage = $subscribeStorage;
        $this->configProvider = $configProvider;
        $this->actionStorage = $actionStorage;
    }

    public function validate(): void
    {
        if (empty($this->configProvider->getCachePath())) {
            $this->runValidation();
            return;
        }

        $dataHash = $this->getDataHash();

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

    private function getDataHash(): string
    {
        return md5(serialize($this->subscribeStorage)) . md5(serialize($this->actionStorage));
    }

    private function isValidCache(string $dataHash): bool
    {
        $filePath = $this->configProvider->getCachePath() . '/' . self::FILE_NAME;

        if (is_file($filePath) === false) {
            return false;
        }

        $hash = file_get_contents($filePath);

        return $hash === $dataHash;
    }

    private function updateCache(string $dataHash): void
    {
        $filePath = $this->configProvider->getCachePath() . '/' . self::FILE_NAME;
        file_put_contents($filePath, $dataHash);
    }
}
