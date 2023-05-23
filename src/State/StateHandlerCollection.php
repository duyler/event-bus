<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

class StateHandlerCollection extends ArrayCollection
{
    public function where(string $key, mixed $value): ArrayCollection
    {
        $expr = new Comparison($key, Comparison::EQ, $value);

        $criteria = new Criteria();

        $criteria->where($expr);

        return $this->matching($criteria);
    }
}
