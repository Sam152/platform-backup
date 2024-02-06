<?php

/**
 * How to use this script:
 *   - Create a new folder in the root of your Platform.sh project called "jobs".
 *   - Run composer init && composer require sam152/platform-backup
 *   - Add AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY and S3_BUCKET to your project variables.
 *   - Copy this file into jobs/run.php and customise to your needs
 *   - Add an entry into your crons that invokes your script as: `php ./jobs/run.php`
 */

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Platformsh\ConfigReader\Config;
use PshBackup\Task\BackupDatabase;
use PshBackup\Task\BackupFilesDirectory;
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

$tasks = [
    new BackupDatabase(
        s3Client: $s3Client,
        localBackupDirectory: sprintf('%s/backups', $platformConfig->appDir),
        destination: new S3Destination(
            getenv('S3_BUCKET'),
            sprintf('platform/%s/%s/database', $platformConfig->applicationName, Inflector::safeS3Prefix($platformConfig->branch))
        ),
        database: $platformConfig->credentials('database'),
        localRetentionDays: 5,
    ),
    new BackupFilesDirectory(
        s3Client: $s3Client,
        sourceDirectory: sprintf('%s/private', $platformConfig->appDir),
        destination: new S3Destination(
            getenv('S3_BUCKET'),
            sprintf('platform/%s/%s/files-private', $platformConfig->applicationName, Inflector::safeS3Prefix($platformConfig->branch))
        )
    ),
    new BackupFilesDirectory(
        s3Client: $s3Client,
        sourceDirectory: sprintf('%s/app/sites/default/files', $platformConfig->appDir),
        destination: new S3Destination(
            getenv('S3_BUCKET'),
            sprintf('platform/%s/%s/files-public', $platformConfig->applicationName, Inflector::safeS3Prefix($platformConfig->branch))
        )
    ),
];

(new TaskRunner(
    logger: (new Logger('backup_logger'))->pushHandler(new StreamHandler('php://stderr')),
))->run($tasks);
