<?php

namespace Application\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Redis;

/**
 * Redis cache provider.
 *
 * This is a copy of Doctrine\Common\Cache\RedisCache but WITHOUT $redis->setOptions()
 *
 * @link   www.doctrine-project.org
 * @since  2.2
 * @author Osman Ungur <osmanungur@gmail.com>
 */
class RedisCache extends CacheProvider
{
    /**
     * @var Redis|null
     */
    private $redis;

    /**
     * Sets the redis instance to use (without changing the options of the injected instance).
     *
     * @param Redis $redis
     *
     * @return void
     */
    public function setRedis(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Gets the redis instance used by the cache.
     *
     * @return Redis|null
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        return $this->redis->get($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetchMultiple(array $keys)
    {
        $fetchedItems = array_combine($keys, $this->redis->mget($keys));

        // Redis mget returns false for keys that do not exist. So we need to filter those out unless it's the real data.
        $foundItems   = array();

        foreach ($fetchedItems as $key => $value) {
            if (false !== $value || $this->redis->exists($key)) {
                $foundItems[$key] = $value;
            }
        }

        return $foundItems;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        if ($lifetime) {
            $success = true;

            // Keys have lifetime, use SETEX for each of them
            foreach ($keysAndValues as $key => $value) {
                if (!$this->redis->setex($key, $lifetime, $value)) {
                    $success = false;
                }
            }

            return $success;
        }

        // No lifetime, use MSET
        return (bool) $this->redis->mset($keysAndValues);
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return $this->redis->exists($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 0) {
            return $this->redis->setex($id, $lifeTime, $data);
        }

        return $this->redis->set($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return $this->redis->delete($id) >= 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return $this->redis->flushDB();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $info = $this->redis->info();
        return array(
            Cache::STATS_HITS   => $info['keyspace_hits'],
            Cache::STATS_MISSES => $info['keyspace_misses'],
            Cache::STATS_UPTIME => $info['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE      => $info['used_memory'],
            Cache::STATS_MEMORY_AVAILABLE  => false
        );
    }
}
