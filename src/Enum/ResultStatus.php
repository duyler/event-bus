<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Enum;

enum ResultStatus: string
{
    case Success = 'Success';
    case Fail = 'Fail';
}
