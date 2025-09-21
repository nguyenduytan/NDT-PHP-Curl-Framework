# AWS Signature V4 (Preview v0.2)

Sign headers to call AWS APIs (S3, API Gateway, etc.).

```php
use ndtan\Curl\Auth\AwsSigV4;

$headers = AwsSigV4::sign(
  'GET', 'https://s3.amazonaws.com/mybucket/mykey',
  [], [], '', 'us-east-1', 's3', 'AKID', 'SECRET'
);

$res = Http::to('https://s3.amazonaws.com/mybucket/mykey')
    ->headers($headers)
    ->get();
```
