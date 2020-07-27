<?php

declare(strict_types=1);

namespace App\ModelFunctions;

use App\Exceptions\NotInCacheException;
use App\Logs;
use Cache;
use Psr\SimpleCache\InvalidArgumentException;

class JsonRequestFunctions
{
    private string $url;
    /**
     * @var array<mixed>|null
     */
    private array $json;
    /**
     * @var string|false
     */
    private $raw;
    private int $ttl;

    /**
     * JsonRequestFunctions constructor.
     *
     * @param string $url Url to request / cache
     * @param int $ttl Time To Live of the cache in DAYS
     */
    public function __construct(string $url, int $ttl = 1)
    {
        $this->url = $url;
        $this->json = \json_decode(Cache::get($url, '')) ?: [];
        $this->ttl = $ttl;
    }

    /**
     * Cache the result of the request.
     */
    private function cache(): void
    {
        try {
            Cache::set($this->url, $this->raw, \now()->addDays($this->ttl));
            Cache::set($this->url . '_age', \now(), \now()->addDays($this->ttl));
        } catch (InvalidArgumentException $e) {
            Logs::error(__METHOD__, (string) __LINE__, 'Could not set in the cache');
        }
    }

    /**
     * Remove elements from the cache.
     */
    public function clear_cache(): void
    {
        Cache::forget($this->url);
        Cache::forget($this->url . '_age');
        $this->json = null;
        $this->raw = null;
    }

    /**
     * return the age of the last query to url.
     *
     * @return mixed
     */
    public function get_age()
    {
        return Cache::get($this->url . '_age');
    }

    /**
     * Return the age of the last query in days/hours/minutes.
     */
    public function get_age_text(): string
    {
        $age = $this->get_age();
        $last = 'unknown';
        $end = '';
        if ($age) {
            $last = \now()->diffInDays($age);
            $end = $last > 0 ? ' days' : '';
            $last = $last === 0 && $end = ' hours' ? \now()->diffInHours($age) : $last;
            $last = $last === 0 && $end = ' minutes' ? \now()->diffInMinutes($age) : $last;
            $last = $last === 0 && $end = ' seconds' ? \now()->diffInSeconds($age) : $last;
            $end .= ' ago';
        }

        return $last . $end;
    }

    /**
     * make the query and cache the result.
     *
     * @return false|array<mixed>
     */
    private function get()
    {
        $opts = [
            'http' => [
                'method' => 'GET',
                'timeout' => 1,
                'header' => ['User-Agent: PHP'],
            ],
        ];
        $context = \stream_context_create($opts);

        /** @var string|false $json */
        $this->raw = @\file_get_contents($this->url, false, $context);

        if ($this->raw !== false) {
            $this->cache();
            $this->json = \json_decode($this->raw);

            return $this->json;
        }
        // @codeCoverageIgnoreStart
        Logs::notice(__METHOD__, (string) __LINE__, 'Could not access: ' . $this->url);
        $this->raw = null;
        $this->json = null;

        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Return the JSON.
     *
     * @return false|array<mixed>
     */
    public function get_json(bool $cached = false)
    {
        if ($cached) {
            if (!$this->json) {
                throw new NotInCacheException();
            }

            return $this->json;
        }

        return $this->get();
    }
}
