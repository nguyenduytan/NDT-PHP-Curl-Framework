<?php
declare(strict_types=1);
namespace ndtan\Curl\Security;
final class CircuitBreaker{
    private array $state=[]; // key => [failures,int; openedAt,int; halfOpen,int]
    public function __construct(private int $failureThreshold=5, private int $coolDown=30, private int $halfOpen=1){}
    public function allow(string $key): bool {
        $s = $this->state[$key] ?? ['failures'=>0,'openedAt'=>0,'halfOpen'=>0];
        if ($s['openedAt']===0) return true; // closed
        $elapsed = time() - $s['openedAt'];
        if ($elapsed >= $this->coolDown) {
            if ($s['halfOpen'] < $this->halfOpen) { $s['halfOpen']++; $this->state[$key]=$s; return true; }
            return false;
        }
        return false;
    }
    public function onSuccess(string $key): void { $this->state[$key] = ['failures'=>0,'openedAt'=>0,'halfOpen'=>0]; }
    public function onFailure(string $key): void {
        $s = $this->state[$key] ?? ['failures'=>0,'openedAt'=>0,'halfOpen'=>0];
        $s['failures']++;
        if ($s['openedAt']===0 && $s['failures'] >= $this->failureThreshold) { $s['openedAt']=time(); }
        $this->state[$key]=$s;
    }
    public function isOpen(string $key): bool { return ($this->state[$key]['openedAt'] ?? 0) > 0; }
}
