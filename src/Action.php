<?php

declare(strict_types=1);

namespace Jine\EventBus;

class Action
{
    public string $name;
    public string $handler;
    public string $serviceId = 'common';
    public array $required = [];
    public array $classMap = [];
    public string $rollback = '';
    public bool $preload = false;
    public bool $repeat = true;

    public function __construct(string $name, string $handler)
    {
        $this->name = $name;
        $this->handler = $handler;
    }

    public function required(array $required): static
    {
        $this->required = $required;
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

    public function repeat(bool $flag): static
    {
        $this->repeat = $flag;
        return $this;
    }

    public function serviceId(string $serviceId): static
    {
        $this->serviceId = $serviceId;
        return $this;
    }
}
