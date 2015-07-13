<?php

namespace Michelv\DoctrineCacheSweeperBundle;

interface CacheSweepableInterface
{
    /**
     * Returns an array of cache keys (strings) related to a given entity
     *
     * @param mixed $entity A Doctrine entity
     * @param string $type Either 'insert', 'update', 'delete', or '*'
     */
    public function getSweepableKeys($entity, $type = '*');
}
