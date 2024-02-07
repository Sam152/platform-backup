<?php

namespace PshBackup\Util;

final class S3Destination {
    public function __construct(public string $bucketName, public string $key) {
    }
    public function uri(): string {
        return sprintf('s3://%s/%s', $this->bucketName, $this->key);
    }
}
