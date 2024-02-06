<?php

namespace PshBackup\Task;

use Aws\S3\S3ClientInterface;
use PshBackup\Util\S3Destination;

final readonly class BackupDatabase implements TaskInterface {

    /**
     * @param array{host: string, password: string, username: string, path: string} $database
     */
    public function __construct(
        private S3ClientInterface $s3Client,
        private string $localBackupDirectory,
        private S3Destination $destination,
        private array $database,
        private int $localRetentionDays,
    ) {
    }

    public function execute(): void {
        $filename = date('Y-m-d_H:i:s') . '.gz';
        $localBackupFilename = sprintf('%s/%s', $this->localBackupDirectory, $filename);

        putenv("MYSQL_PWD={$this->database['password']}");
        exec("mysqldump --opt -h {$this->database['host']} -u {$this->database['username']} {$this->database['path']} | gzip > $localBackupFilename");

        if (!file_exists($localBackupFilename)) {
            throw new \Exception('Failed to create the local database backup file.');
        }

        $this->s3Client->putObject([
            'Bucket' => $this->destination->bucketName,
            'Key' => sprintf("%s/%s", $this->destination->key, $filename),
            'Body' => fopen($localBackupFilename, 'r'),
        ]);

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
