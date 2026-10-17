<?php
namespace Bato\Queue;

class RedisQueue
{
    private \Redis $redis;
    private string $key;

    public function __construct(array $config = [])
    {
        $host = $config['host'] ?? getenv('REDIS_HOST') ?: '127.0.0.1';
        $port = $config['port'] ?? (int)(getenv('REDIS_PORT') ?: 6379);
        $this->key = $config['key'] ?? 'bato:jobs';
        $this->redis = new \Redis();
        $this->redis->connect($host, $port);
        if (!empty($config['password'])) {
            $this->redis->auth($config['password']);
        }
    }

    public function push(array $job): bool
    {
        return $this->redis->lPush($this->key, json_encode($job));
    }

    public function pop(int $timeout = 5): ?array
    {
        $res = $this->redis->brPop($this->key, $timeout);
        if (!$res) return null;
        // brPop returns [key, value]
        $payload = $res[1] ?? null;
        if (!$payload) return null;
        return json_decode($payload, true);
    }
}
