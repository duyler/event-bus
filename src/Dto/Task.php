<?php 

declare(strict_types=1);

namespace Jine\EventBus\Dto;

class Task
{
    public string $serviceId;
    public string $action;
    public string $handler = '';
    public array $required = [];
    public array $classMap = [];
    public string $rollback = '';
    public bool $repeat = false;
}
