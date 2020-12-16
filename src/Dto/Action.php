<?php

declare(strict_types=1);

namespace Jine\EventBus\Dto;

class Action
{
    public string $name;
    public string $serviceId;
    public string $handler = '';
    public array $required = [];
    public array $classMap = [];
    public string $rollback = '';
    public bool $preload = false;

    public function __construct(string $name, string $serviceId)
    {
        $this->name = $name;
        $this->serviceId = $serviceId;
    }

    public function required(array $required): static
    {
        $this->required = $required;
        return $this;
    }

    public function handler(string $handler): static
    {
        $this->handler = $handler;
        return $this;
    }

    public function classMap(array $classMap): static
    {
        $this->classMap = $classMap;
    }

    public function rollback(string $rollbackHandler): static
    {
        $this->rollback = $rollbackHandler;
    }

    public function preload(bool $flag): static
    {
        $this->preload = $flag;
        return $this;
    }
}
