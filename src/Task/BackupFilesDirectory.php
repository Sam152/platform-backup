<?php

namespace PshBackup\Task;

use Aws\S3\S3ClientInterface;
use PshBackup\Util\S3Destination;

final readonly class BackupFilesDirectory implements TaskInterface {

    public function __construct(
        private S3ClientInterface $s3Client,
        private string $sourceDirectory,
        private S3Destination $destination,
    ) {
    }

    public function execute(): void {
        $this->s3Client->uploadDirectory($this->sourceDirectory, $this->destination->uri());
    }
}
