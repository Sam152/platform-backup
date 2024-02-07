<?php

namespace PshBackup\Task;

/**
 * Can be inserted into the task list for testing purposes:
 *   - Do other tasks continue when one fails?
 *   - Are the correct logger channels invoked? Ie, perhaps we have an email logger configured for critical errors.
 */
final readonly class ThrowException implements TaskInterface {

    public function __construct() {
    }

    public function execute(): void {
        throw new \Exception('Something went wrong.');
    }
}
