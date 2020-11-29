<?php

declare(strict_types=1);

namespace Jine\EventBus\Dto;

use Jine\EventBus\Contract\ResultInterface;

class Result implements ResultInterface
{
    public const STATUS_SUCCESS = 'Success';
    public const STATUS_FAIL = 'Fail';

    public string $status;
    public object|null $data = null;

    public function __construct(string $status, object $data = null)
    {
        $this->status = $status;
        $this->data = $data;
    }
}
