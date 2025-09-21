<?php
declare(strict_types=1);
namespace ndtan\Curl\Core;

use ndtan\Curl\Http\Response;
use ndtan\Curl\Support\HeaderBag;
use ndtan\Curl\Support\Json;
use ndtan\Curl\Security\Backoff;
use ndtan\Curl\Security\CircuitBreaker;
use ndtan\Curl\Security\RateLimiter;
use ndtan\Curl\Cache\HttpCache;
use ndtan\Curl\Vcr\Vcr;
use Psr\SimpleCache\CacheInterface;
use Psr\Log\LoggerInterface;

final class RequestBuilder
{
    private string $method = 'GET';
    private string $url;
    private array $query = [];
    private HeaderBag $headers;
    private mixed $body = null;
    private ?string $mode = null; // 'json'|'form'|'multipart'|'raw'
    private array $multipart = [];
    private array $opts = [];
    private ?string $savePath = null;
    private ?int $maxBodySize = null;
    private ?int $hedgeAfterMs = null;
    private int $hedgeMax = 0;
    private int $retryTimes = 0;
    private string $retryStrategy = 'decorrelated';
    private int $retryBaseMs = 100;
    private int $retryMaxMs = 2000;
    private bool $retryJitter = true;
    private bool $followRedirects = false;
    private int $maxRedirects = 10;
    private bool $allowCrossHostRedirects = false;
    private array $preserveAuthOn = [307,308];
    private bool $decompression = true;
    private bool $keepalive = true;
    private ?string $cookieJar = null;
    private string $cookieFormat = 'auto';
    private bool $cookiePersist = true;
    private ?string $proxy = null;
    private ?string $noProxy = null;
    private array $tls = ['min'=>'TLSv1.2','ciphers'=>null,'pinned_pubkey'=>null,'verify_peer'=>true,'verify_host'=>2];
    private ?CacheInterface $cache = null;
    private ?HttpCache $httpCache = null;
    private ?Vcr $vcr = null;
    private ?CircuitBreaker $circuit = null;
    private ?RateLimiter $rLimiter = null;
    private ?LoggerInterface $logger = null;
    private array $hooks = ['onRequest'=>[], 'onResponse'=>[], 'onRetry'=>[], 'onRedirect'=>[]];
    private ?float $deadline = null;
    private ?int $resumeFrom = null;

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->headers = new HeaderBag();
    }

    // ------- fluent API -------
    public function method(string $m): self { $this->method = strtoupper($m); return $this; }
    public function get(string $pathOrUrl=null, array $query=[]): Response { if($pathOrUrl) $this->to($pathOrUrl); if($query) $this->query($query); $this->method('GET'); return $this->send(); }
    public function post(string $pathOrUrl=null, mixed $data=null): Response { if($pathOrUrl) $this->to($pathOrUrl); if($data!==null) $this->data($data); $this->method('POST'); return $this->send(); }
    public function put(string $pathOrUrl=null, mixed $data=null): Response { if($pathOrUrl) $this->to($pathOrUrl); if($data!==null) $this->data($data); $this->method('PUT'); return $this->send(); }
    public function patch(string $pathOrUrl=null, mixed $data=null): Response { if($pathOrUrl) $this->to($pathOrUrl); if($data!==null) $this->data($data); $this->method('PATCH'); return $this->send(); }
    public function delete(string $pathOrUrl=null): Response { if($pathOrUrl) $this->to($pathOrUrl); $this->method('DELETE'); return $this->send(); }

    public function to(string $url): self { $this->url = $url; return $this; }
    public function baseUrl(string $base): self { if (!str_starts_with($this->url,'http')) $this->url = rtrim($base,'/').'/'.ltrim($this->url,'/'); return $this; }
    public function query(array $params): self { $this->query = array_merge($this->query, $params); return $this; }
    public function headers(array $h): self { foreach($h as $k=>$v) $this->headers->set($k,$v); return $this; }
    public function header(string $k, string|array $v): self { $this->headers->set($k,$v); return $this; }

    public function asJson(): self { $this->mode='json'; $this->headers->set('Accept','application/json'); return $this; }
    public function sendJson(mixed $data): self { $this->mode='json'; $this->data($data); return $this; }
    public function expectJson(): self { $this->headers->set('Accept','application/json'); return $this; }
    public function jsonFlags(int $flags): self { \ndtan\Curl\Support\Json::$encodeFlags = $flags; return $this; }
    public function jsonAssoc(bool $assoc): self { \ndtan\Curl\Support\Json::$decodeAssoc = $assoc; return $this; }

    public function asForm(): self { $this->mode='form'; $this->headers->set('Content-Type','application/x-www-form-urlencoded'); return $this; }
    public function multipart(array $parts): self { $this->mode='multipart'; $this->multipart=$parts; return $this; }
    public function data(mixed $data): self { $this->body=$data; return $this; }

    public function cookieJar(string $path, string $format='auto', bool $persist=true): self { $this->cookieJar=$path; $this->cookieFormat=$format; $this->cookiePersist=$persist; return $this; }
    public function proxy(string $url, ?string $noProxy=null): self { $this->proxy=$url; $this->noProxy=$noProxy; return $this; }

    public function retry(int $times): self { $this->retryTimes=$times; return $this; }
    public function backoff(string $strategy='decorrelated', int $baseMs=100, int $maxMs=2000, bool $jitter=true): self { $this->retryStrategy=$strategy; $this->retryBaseMs=$baseMs; $this->retryMaxMs=$maxMs; $this->retryJitter=$jitter; return $this; }
    public function hedge(?int $afterMs, int $max=1): self { $this->hedgeAfterMs=$afterMs; $this->hedgeMax=$max; return $this; }

    public function redirects(int $max=10, bool $crossHost=false, array $preserveAuthOn=[307,308]): self { $this->followRedirects=true; $this->maxRedirects=$max; $this->allowCrossHostRedirects=$crossHost; $this->preserveAuthOn=$preserveAuthOn; return $this; }

    public function tls(array $opts): self { $this->tls = array_merge($this->tls, $opts); return $this; }
    public function deadline(float $unixSeconds): self { $this->deadline = $unixSeconds; return $this; }
    public function resumeFromBytes(int $offset): self { $this->resumeFrom = $offset; return $this; }

    public function decompress(bool $on=true): self { $this->decompression=$on; return $this; }
    public function keepalive(bool $on=true): self { $this->keepalive=$on; return $this; }
    public function maxBodySize(?int $bytes): self { $this->maxBodySize=$bytes; return $this; }

    public function saveTo(string $path): self { $this->savePath=$path; return $this; }

    public function opt(int $opt, mixed $value): self { $this->opts[$opt]=$value; return $this; }
    public function opts(array $opts): self { $this->opts = array_replace($this->opts, $opts); return $this; }

    public function cache(CacheInterface $psr16, int $defaultTtl=60): self { $this->cache=$psr16; $this->httpCache = new HttpCache($psr16,$defaultTtl); return $this; }
    public function vcr(?Vcr $vcr): self { $this->vcr=$vcr; return $this; }
    public function circuit(?CircuitBreaker $cb): self { $this->circuit=$cb; return $this; }
    public function rateLimiter(?RateLimiter $rl): self { $this->rLimiter=$rl; return $this; }
    public function logger(?LoggerInterface $logger): self { $this->logger=$logger; return $this; }

    public function on(string $event, callable $cb): self { $this->hooks[$event][] = $cb; return $this; }

    // ------- execution -------
    public function send(): Response
    {
        $attempt = 0;
        $serviceKey = parse_url($this->url, PHP_URL_HOST) ?: 'default';

        start:
        $attempt++;
        foreach ($this->hooks['onRequest'] as $cb) { try { $cb($this); } catch (\Throwable $e){} }

        if ($this->circuit && !$this->circuit->allow($serviceKey)) {
            throw new \RuntimeException('Circuit open for '.$serviceKey);
        }

        $res = $this->doSend();

        // Redirect handling (manual to control auth/header policy)
        $redirCount = 0;
        while ($this->followRedirects && in_array($res->status(), [301,302,303,307,308], true) && $redirCount < $this->maxRedirects) {
            $location = $res->header('location');
            if (!$location) break;
            $newUrl = self::absUrl($this->url, is_array($location)?$location[0]:$location);
            $sameOrigin = (parse_url($this->url, PHP_URL_HOST) === parse_url($newUrl, PHP_URL_HOST));
            if (!$sameOrigin && !$this->allowCrossHostRedirects) break;
            $preserveAuth = in_array($res->status(), $this->preserveAuthOn, true) && $sameOrigin;
            if (!$preserveAuth) {
                $this->headers->set('Authorization', '');
            }
            $this->url = $newUrl;
            $this->method = ($res->status()===303)?'GET':$this->method;
            $redirCount++;
            foreach ($this->hooks['onRedirect'] as $cb) { try { $cb($this, $res); } catch(\Throwable $e){} }
            $res = $this->doSend();
        }

        if ($this->circuit) {
            if ($res->ok()) $this->circuit->onSuccess($serviceKey); else $this->circuit->onFailure($serviceKey);
        }

        if (!$res->ok() && $attempt <= $this->retryTimes) {
            foreach ($this->hooks['onRetry'] as $cb) { try { $cb($this, $res, $attempt); } catch(\Throwable $e){} }
            $sleep = Backoff::decorrelatedJitter($attempt, $this->retryBaseMs, $this->retryMaxMs);
            usleep($sleep * 1000);
            goto start;
        }

        foreach ($this->hooks['onResponse'] as $cb) { try { $cb($res); } catch (\Throwable $e){} }
        return $res;
    }

    private static function absUrl(string $base, string $location): string {
        if (str_starts_with($location, 'http')) return $location;
        $p = parse_url($base);
        $scheme = $p['scheme'] ?? 'http';
        $host = $p['host'] ?? '';
        $port = isset($p['port'])?':'.$p['port']:'';
        if (str_starts_with($location, '/')) return $scheme+'://'+$host+$port+$location;
        $path = rtrim(dirname($p['path'] ?? '/'),'/').'/'.$location;
        return $scheme+'://'+$host+$port+$path;
    }

    private function buildBodyAndHeaders(): array
    {
        $body = '';
        $headers = $this->headers->all();

        if ($this->mode==='json') {
            $body = is_string($this->body) ? $this->body : Json::encode($this->body ?? []);
            $headers['Content-Type'] = 'application/json';
        } elseif ($this->mode==='form') {
            $body = is_array($this->body) ? http_build_query($this->body) : (string)$this->body;
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        } elseif ($this->mode==='multipart') {
            $body = $this->multipart;
        } else {
            $body = $this->body ?? '';
        }
        return [$body, $headers];
    }

    private function applyCommonOpts($ch): void
    {
        $headers = $this->headers->all();
        $query = $this->query ? '?'.http_build_query($this->query) : '';
        $url = $this->url . $query;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        if ($this->decompression) {
            curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, '');
        }

        if ($this->cookieJar && $this->cookiePersist) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieJar);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieJar);
        }

        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
            if ($this->noProxy) curl_setopt($ch, CURLOPT_NOPROXY, $this->noProxy);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->tls['verify_peer'] ?? true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->tls['verify_host'] ?? 2);
        if (!empty($this->tls['ciphers'])) curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, $this->tls['ciphers']);
        if (!empty($this->tls['pinned_pubkey']) and defined('CURLOPT_PINNEDPUBLICKEY')) {
            curl_setopt($ch, CURLOPT_PINNEDPUBLICKEY, $this->tls['pinned_pubkey']);
        }
        if (defined('CURLOPT_HAPPY_EYEBALLS_TIMEOUT_MS')) {
            curl_setopt($ch, CURLOPT_HAPPY_EYEBALLS_TIMEOUT_MS, 200);
        }

        if (($this->opts.get(CURLOPT_HTTP_VERSION) if hasattr(self,'opts') else None) is None):
            pass

        # Headers
        hdr = []
        for k,v in headers.items():
            if v == '':
                continue
            if isinstance(v, list):
                for vv in v:
                    hdr.append(f"{k}: {vv}")
            else:
                hdr.append(f"{k}: {v}")
        curl_setopt($ch, CURLOPT_HTTPHEADER, hdr)

        if ($this->resumeFrom) curl_setopt($ch, CURLOPT_RESUME_FROM, $this->resumeFrom);

        foreach ($this->opts as $k=>$v) {
            if (is_int($k)) curl_setopt($ch, $k, $v);
        }
    }

    private function doSend(): Response
    {
        [$body, $headers] = $this->buildBodyAndHeaders();
        foreach ($headers as $k=>$v) $this->headers->set($k,$v);

        if ($this->vcr && $this->vcr->mode()==='replay') {
            $match = $this->vcr->find($this->method, $this->url, is_string($body)?$body:'');
            if ($match) {
                return new Response($match['response']['status'], $match['response']['headers'], $match['response']['body'], $this->url, $match['response']['timings'] ?? []);
            }
        }

        $ch = curl_init();
        $this->applyCommonOpts($ch);

        if ($this->method==='GET' || $this->method==='HEAD') {
            // nothing
        } else {
            if ($this->mode==='multipart' && is_array($body)) {
                $mp = [];
                foreach ($body as $k=>$v) {
                    $mp[$k] = ($v instanceof \CURLFile) ? $v : $v;
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $mp);
            } elseif (is_resource($body)) {
                curl_setopt($ch, CURLOPT_UPLOAD, true);
                curl_setopt($ch, CURLOPT_INFILE, $body);
                $stat = fstat($body);
                if ($stat && isset($stat['size'])) curl_setopt($ch, CURLOPT_INFILESIZE, $stat['size']);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body)?$body:(string)$body);
            }
        }

        $respHeaders = '';
        $respBody = '';
        $headerFn = function($ch, string $header) use (&$respHeaders) {
            $respHeaders .= $header;
            return strlen($header);
        };
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, $headerFn);

        $bytes = 0;
        if ($this->savePath) {
            $fp = fopen($this->savePath, 'ab');
            $writeFn = function($ch, string $chunk) use (&$bytes, $fp) {
                $len = strlen($chunk);
                $bytes += $len;
                fwrite($fp, $chunk);
                return $len;
            };
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, $writeFn);
        } else {
            $writeFn = function($ch, string $chunk) use (&$respBody, &$bytes) {
                $len = strlen($chunk);
                $bytes += $len;
                $respBody .= $chunk;
                return $len;
            };
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, $writeFn);
        }

        $ok = curl_exec($ch);
        $info = curl_getinfo($ch);
        $errNo = curl_errno($ch);
        $err = curl_error($ch);

        if ($this->savePath ?? false) { fclose($fp); }

        $headerSize = $info['header_size'] ?? 0;
        $raw = $this->savePath ? '' : $respBody;
        $rawHeaders = substr($raw, 0, $headerSize) ?: $respHeaders;
        $rawBody = $this->savePath ? '' : substr($raw, $headerSize);

        $headersArr = [];
        foreach (explode("\r\n", trim($rawHeaders)) as $line) {
            if (stripos($line, 'HTTP/')===0) continue;
            if (str_contains($line, ':')) {
                [$k,$v]=explode(':', $line, 2);
                $headersArr[strtolower(trim($k))] = trim($v);
            }
        }

        $status = (int)($info['http_code'] ?? 0);
        $effectiveUrl = (string)($info['url'] ?? $this->url);

        $timings = [
            'dns' => ($info['namelookup_time'] ?? 0.0),
            'connect' => ($info['connect_time'] ?? 0.0),
            'tls' => max(0.0, ($info['appconnect_time'] ?? 0.0) - ($info['connect_time'] ?? 0.0)),
            'ttfb' => ($info['starttransfer_time'] ?? 0.0),
            'first_byte' => ($info['starttransfer_time'] ?? 0.0),
            'transfer' => max(0.0, ($info['total_time'] ?? 0.0) - ($info['starttransfer_time'] ?? 0.0)),
            'total' => ($info['total_time'] ?? 0.0),
        ];

        if ($errNo) {
            curl_close($ch);
            throw new \RuntimeException('cURL error #'.$errNo.': '.$err);
        }

        if ($this->maxBodySize !== null && $bytes > $this->maxBodySize) {
            curl_close($ch);
            throw new \RuntimeException('Body exceeded max size');
        }

        curl_close($ch);

        $response = new Response($status, $headersArr, $rawBody, $effectiveUrl, $timings);

        if ($this->vcr && $this->vcr->mode()!=='replay') {
            $this->vcr->save($this->method, $this->url, ['headers'=>$this->headers->all(),'body'=>is_string($body)?$body:''], [
                'status'=>$status,'headers'=>$headersArr,'body'=>$rawBody,'timings'=>$timings
            ]);
        }

        return $response;
    }
}
