<?php

namespace Tests\PshBackup;

use Aws\S3\S3ClientInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use PshBackup\Task\BackupFilesDirectory;
use PshBackup\Util\S3Destination;

class UploadFilesDirectoryTest extends TestCase {
    use ProphecyTrait;

    public function test_upload_command_is_called_with_correct_paths() {
        $s3Client = $this->prophesize(S3ClientInterface::class);
        $s3Client->uploadDirectory('/foo/bar', 's3://foo-bucket/bar/path')->shouldBeCalled();

        $uploadTask = new BackupFilesDirectory($s3Client->reveal(), '/foo/bar', new S3Destination('foo-bucket', 'bar/path'));
        $uploadTask->execute();
    }

}
