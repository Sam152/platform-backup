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
        private string $localBackupDirectory,
        private S3Destination $destination,
        private \DateTimeInterface $yesterday,
        private string $logDatePattern,
    ) {
    }

    public function execute(): void {
        $logFileName = basename($this->sourceLogFile);
        $localBackupFilePath = sprintf('%s/%s-%s', $this->localBackupDirectory, $logFileName, $this->yesterday->format('Y-m-d'));
        $searchDatePattern = $this->yesterday->format($this->logDatePattern);

        exec("cat $this->sourceLogFile | grep $searchDatePattern > $localBackupFilePath");

        if (!file_exists($localBackupFilePath)) {
            throw new \Exception('Log file was not created.');
        }

        $this->s3Client->putObject([
            'Bucket' => $this->destination->bucketName,
            // Store in a location something like the following: `some-s3-location/2024/02/22-app.log`.
            'Key' => sprintf("%s/%s/%s/%s-%s", $this->destination->key, $this->yesterday->format('Y'), $this->yesterday->format('m'), $this->yesterday->format('d'), $logFileName),
            'Body' => fopen($localBackupFilePath, 'r'),
        ]);

    }
}
