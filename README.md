
Warning: Backups are only as good as the last time you tested a restoration. All backup systems should
be monitored and tested regularly.

## The script

Automated backup script that pulls the database, compresses, and syncs to an
S3 bucket. Designed for Platform.sh

## Install

You can either clone this repo and use as its own project or you can require it as a dependency into your existing project.

`composer require sam152/platform-backup`

## Setup

- composer install
- Create IAM user with write access to a S3 bucket.
- Add backups directory to .platform.app.yaml
```
mounts:
    "/backups": "shared:files/backups"
```

- Add environmental variables in Platform.sh. Be sure to add the "env:" prefix.
    - env:AWS_ACCESS_KEY_ID
    - env:AWS_SECRET_ACCESS_KEY
    - env:S3_BUCKET (The name of the bucket you created)
    - env:AWS_REGION (Optional, defaults to us-east-1)

- Add composer install to .platform.app.yaml
```
hooks:
    build: |
        composer install --working-dir=./jobs
```
  - Copy 'example_backup_script.php' to './jobs/backup.php' and modify it for your needs.
  - Deploy and test using: php ./jobs/backup.php
  - Add cron task to .platform.app.yml

```
db_backup:
    spec: "0 0 * * *"
    cmd: "php ./jobs/backup.php"
```

Note, you might have to update the cmd to point to a different location depending on how you installed.

#### Credits

Adapted from https://github.com/benjy/platform-backup
Which was adapted from https://bitbucket.org/snippets/kaypro4/gnB4E
With inspiration from https://gitlab.com/contextualcode/platformsh-store-logs-at-s3

## Licence

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
