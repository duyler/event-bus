<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Contract\RollbackInterface;

class Rollback extends Container
{
    public function run(array $completeTasks)
    {
        foreach ($completeTasks as $task) {
            if (empty($task->rollback)) {
                continue;
            }
            $this->rollback($this->instance($task->rollback));
        }
    }
    
    private function rollback(RollbackInterface $rollback)
    {
        $rollback->run();
    }
}

