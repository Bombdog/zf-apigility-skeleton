<?php

namespace Application\Cache;

/**
 * A cache service to store objects (as strings).
 * Your object should be safely serializable.
 *
 * @see http://docs.doctrine-project.org/projects/doctrine-mongodb-odm/en/latest/reference/architecture.html#serializing-documents
 * @see http://docs.doctrine-project.org/projects/doctrine-mongodb-odm/en/latest/cookbook/implementing-wakeup-or-clone.html
 *
 */
final class ObjectCache extends RedisCache
{
    /**
     * ObjectCache constructor.
     * Takes a redis connection and gives a default namespace.
     * Ideally you should supply your own namespace via setNamespace.
     *
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->setRedis($redis);
        $this->setNamespace('ObjectCache');
    }

    /**
     * Save function defaults to half an hour cache time.
     *
     * @param string $id
     * @param mixed $data
     * @param int $lifeTime
     *
     * @return bool
     */
    public function save($id, $data, $lifeTime = 1800)
    {
        return parent::save($id, $data, $lifeTime);
    }
}
