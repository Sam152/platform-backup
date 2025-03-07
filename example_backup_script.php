<?php

/**
 * This is an example implementation. Copy it into your project and modify it for your needs.
 */

require __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Platformsh\ConfigReader\Config;
use PshBackup\Task\BackupDailyLogs;
use PshBackup\Task\BackupDatabase;
use PshBackup\Task\BackupFilesDirectory;
use PshBackup\Task\CleanBackupFolder;
use PshBackup\TaskRunner;
use PshBackup\Util\Inflector;
use PshBackup\Util\S3Destination;

$platformConfig = new Config();
$s3Client = new S3Client([
    'version' => '2006-03-01',
    'region' => 'us-east-1',
    'credentials' => [
        'key' => getenv('AWS_ACCESS_KEY_ID'),
        'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
    ],
]);
$localBackupDirectory = sprintf('%s/backups', $platformConfig->appDir);
$safeBranchName = Inflector::safeS3Prefix($platformConfig->branch);

$tasks = [
    new BackupDatabase(
        s3Client: $s3Client,
        localBackupDirectory: $localBackupDirectory,
        destination: new S3Destination(
            getenv('S3_BUCKET'),
            sprintf('platform/%s/%s/database', $platformConfig->applicationName, $safeBranchName)
        ),
        database: $platformConfig->credentials('database'),
    ),
    new BackupFilesDirectory(
        s3Client: $s3Client,
        sourceDirectory: sprintf('%s/private', $platformConfig->appDir),
        destination: new S3Destination(
            getenv('S3_BUCKET'),
            sprintf('platform/%s/%s/files-private', $platformConfig->applicationName, $safeBranchName)
        )
    ),
    new BackupFilesDirectory(
        s3Client: $s3Client,
        sourceDirectory: sprintf('%s/app/sites/default/files', $platformConfig->appDir),
        destination: new S3Destination(
            getenv('S3_BUCKET'),
            sprintf('platform/%s/%s/files-public', $platformConfig->applicationName, $safeBranchName)
        )
    ),
    new BackupDailyLogs(
        s3Client: $s3Client,
        sourceLogFile: '/var/log/app.log',
        localBackupDirectory: $localBackupDirectory,
        destination: new S3Destination(
            getenv('S3_BUCKET'),
            sprintf('platform/%s/%s/logs', $platformConfig->applicationName, $safeBranchName)
        ),
        // If "yesterday" was 12 hours ago, this command must be run in the first 12 hours of the day (UTC).
        yesterday: (new DateTimeImmutable())->sub(new \DateInterval('PT12H')),
        logDatePattern: 'Y-m-d',
    ),
    new CleanBackupFolder(
        localBackupDirectory: $localBackupDirectory,
        localRetentionDays: 5,
    ),
];

(new TaskRunner(
    logger: (new Logger('backup_logger'))
        ->pushHandler(new StreamHandler('php://stderr'))
        // Email someone when a critical error is logged, ie one of the tasks failed.
        ->pushHandler(new NativeMailerHandler('devs@example.com', 'Critical error from backup system', 'devs@example.com'))
))->run($tasks);
