<?php

namespace Michelv\DoctrineCacheSweeperBundle;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Common\Cache\Cache;

class CacheSweeper
{
    protected $services = array();

    /**
     * Allow any service or class that implements CacheSweepableInterface
     * to provide cache keys to clear
     *
     * @param CacheSweepableInterface $service
     */
    public function addService(CacheSweepableInterface $service)
    {
        $this->services[] = $service;
    }

    /**
     * Gather the cache keys to clear
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $cache = $em->getConfiguration()->getResultCacheImpl();

        if (!($cache instanceof Cache)) {
            // no result cache, no point gathering cache keys
            return;
        }

        $uow = $em->getUnitOfWork();

        $operations = array(
            'insert' => $uow->getScheduledEntityInsertions(),
            'update' => $uow->getScheduledEntityUpdates(),
            'delete' => $uow->getScheduledEntityDeletions(),
        );

        $keys = array();

        foreach ($operations as $type => $entities) {
            foreach ($entities as $entity) {
                $repository = $em->getRepository(get_class($entity));
                if ($repository instanceof CacheSweepableInterface) {
                    $keys = array_merge($keys, $repository->getSweepableKeys($entity, $type));
                }

                foreach ($this->services as $service) {
                    $keys = array_merge($keys, $service->getSweepableKeys($entity, $type));
                }
            }
        }

        $this->clearCache($cache, $keys);
    }

    /**
     * Delete the cache keys
     *
     * @param Cache $cache
     * @param array $keys
     */
    protected function clearCache(Cache $cache, $keys)
    {
        $keys = array_unique($keys);

        foreach ($keys as $key) {
            $cache->delete($key);
        }
    }
}
