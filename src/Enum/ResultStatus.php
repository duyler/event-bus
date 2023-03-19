<?php

declare(strict_types=1);

namespace Duyler\EventBus\Enum;

enum ResultStatus: string
{
    case Success = 'Success';
    case Fail = 'Fail';
}
