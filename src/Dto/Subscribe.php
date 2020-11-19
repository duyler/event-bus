<?php

declare(strict_types=1);

namespace Jine\EventBus\Dto;

class Subscribe
{
    public string $subject;

    public string $actionFullName;
    
    public function __construct(string $subject, string $actionFullName)
    {
        $this->subject = $subject;
        $this->actionFullName = $actionFullName;
    }
}
