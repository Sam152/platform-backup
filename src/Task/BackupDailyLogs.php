<?php

namespace PshBackup\Task;

use Aws\S3\S3ClientInterface;
use PshBackup\Util\S3Destination;

/**
 * Limitations:
 *  - Only captures logs matching the date format, not command output from things like workers and cron jobs.
 */
final readonly class BackupDailyLogs implements TaskInterface {

    public function __construct(
        private S3ClientInterface $s3Client,
        private string $sourceLogFile,
        private S3Destination $destination,
        private \DateTimeInterface $yesterday,
        private string $logDatePattern,
    ) {
    }

    /**
     * Adapt from: https://gitlab.com/contextualcode/platformsh-store-logs-at-s3
     */
    public function execute(): void {

//        $this->s3Client->putObject([
//            'Bucket' => $this->destination->bucketName,
//            'Key' => sprintf("%s/%s", $this->destination->key, $filename),
//            'Body' => fopen($localBackupFilename, 'r'),
//        ]);

    }
}
