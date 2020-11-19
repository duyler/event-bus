<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Service;
use RuntimeException;

use function array_key_exists;

class ServiceStorage
{
    // Массив участников
    private array $services = [];
    
    // Регистрирует участника в репозитории шиныF
    public function save(Service $service) : void
    {
        if (array_key_exists($service->id, $this->services)) {
            throw new RuntimeException('Service ' . $service . 'already registered');
        }

        $this->services[$service->id] = $service;
    }

    public function getService(string $serviceId): Service
    {
        return $this->services[$serviceId];
    }
    
    public function has(string $serviceId): bool
    {
        return array_key_exists($serviceId, $this->services);
    }
}
