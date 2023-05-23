<?php

namespace Duyler\EventBus\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Duyler\EventBus\Action\ActionContainer;

class ActionContainerCollection extends ArrayCollection
{
    public function get(string|int $key): ActionContainer
    {
        return $this->where('actionId', $key)->first();
    }

    public function where(string $key, mixed $value): ArrayCollection
    {
        $criteria = new Criteria();

        $criteria->where(
            new Comparison($key, Comparison::EQ, $value)
        );

        return $this->matching($criteria);
    }
}
