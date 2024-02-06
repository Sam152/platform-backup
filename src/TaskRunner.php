<?php

namespace PshBackup;

use Psr\Log\LoggerInterface;

readonly final class TaskRunner {

    public function __construct(private LoggerInterface $logger) {
    }

    /**
     * @param \PshBackup\Task\TaskInterface[] $tasks
     */
    public function run(array $tasks): void {
        foreach ($tasks as $task) {
            try {
                $this->logger->notice('Starting execution of task', ['task' => get_class($task)]);
                $task->execute();
            }
            catch (\Exception) {
                $this->logger->critical('Execution of task failed', ['task' => get_class($task)]);
            }
        }
    }
}
