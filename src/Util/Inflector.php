<?php

namespace PshBackup\Util;

readonly final class Inflector {
    public static function safeS3Prefix(string $input): string {
        return strtolower(preg_replace('/[\W\s\/]+/', '-', $input));
    }
}
