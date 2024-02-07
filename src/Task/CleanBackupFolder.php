<?php

namespace PshBackup\Task;

final readonly class CleanBackupFolder implements TaskInterface {

    public function __construct(
        private string $localBackupDirectory,
        private int $localRetentionDays,
    ) {
    }

    public function execute(): void {
        // Remove files in the local backups folder older than our retention period.
        $fileSystemIterator = new \FilesystemIterator($this->localBackupDirectory);
        $now = time();
        foreach ($fileSystemIterator as $file) {
            if ($now - $file->getCTime() >= 60 * 60 * 24 * $this->localRetentionDays) {
                unlink(sprintf('%s/%s', $this->localBackupDirectory, $file->getFilename()));
            }
        }
    }
}
