# Large Files (Download/Upload)

## Download (streaming + resume)
```php
Http::to('https://cdn.example.com/big.iso')
  ->resumeFromBytes(filesize('/tmp/big.iso') ?: 0)
  ->saveTo('/tmp/big.iso')
  ->get();
```

## Upload (streaming)
```php
Http::to('https://api.example.com/put')
  ->data(fopen('/path/1GB.bin','rb'))
  ->put();

Http::to('https://api.example.com/upload')
  ->multipart(['file' => Http::file('/path/big.iso')])
  ->post();
```
