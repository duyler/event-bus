<?php

declare(strict_types=1);

namespace Jine\EventBus\Dto;

use Jine\EventBus\Contract\ResultInterface;

class Result implements ResultInterface
{
    public const STATUS_SUCCESS = 'Success';
    public const STATUS_FAIL = 'Fail';
    public const STATUS_AWAIT = 'Await';

    public string $status;
    public $data = null;

    public function __construct(string $status, $data = null)
    {
        $this->status = $status;
        $this->data = $data;
    }
}
