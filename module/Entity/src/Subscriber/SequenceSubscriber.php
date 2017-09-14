<?php

namespace Entity\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Entity\Document\Traits\SequenceTrait;

/**
 * Sequence Subscriber
 * Any entities with a sequence field will be given a value at the prePersist stage.
 * Currently the sequence is not scalable, ie. if we have more than one mongo instance
 * this class will have to be revised, probably to use Redis instead. It should be trivial
 * to move our sequences to Redis instead should the need arise.
 *
 * @see http://shiflett.org/blog/2010/jul/auto-increment-with-mongodb
 */
class SequenceSubscriber implements EventSubscriber
{
    const SEQUENCE_START = 1000;
    const TRAIT_SEQUENECE = 'Entity\Document\Traits\SequenceTrait';

    /**
     * Return an array of events this subscriber wants to listen to.
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::prePersist];
    }

    /**
     * New entities
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var SequenceTrait $entity */
        $entity = $args->getObject();
        $traits = class_uses($entity);

        if (in_array(self::TRAIT_SEQUENECE, $traits)) {
            if ($entity->getSequence() === null) {
                $collection = $args->getDocumentManager()
                    ->getDocumentCollection('Entity\Document\Sequence')
                    ->getMongoCollection();

                $next = $this->getNextValue($collection, get_class($entity));
                $entity->setSequence($next);
            }
        }
    }

    /**
     * Get the next sequence value for the current entity.
     *
     * @param \MongoCollection $coll
     * @param $className
     *
     * @return int
     */
    private function getNextValue(\MongoCollection $coll, $className)
    {
        $key = substr($className, strrpos($className, "\\") + 1);
        $next = $this->getSequence($coll, $key);
        if ($next) {
            return $next;
        }

        # If there is no existing sequence we seed and try again
        #  (note: tried an "insert or ignore" strategy here but it didn't work)
        $insertDocs = [["_id" => $key, "seq" => self::SEQUENCE_START]];
        $coll->batchInsert(
            $insertDocs,
            ['continueOnError' => 1]
        );

        return $this->getSequence($coll, $key);
    }

    /**
     * Get the next sequence from Mongo (or zero if it does not exist)
     *
     * @param \MongoCollection $coll
     * @param string $key
     *
     * @return int
     */
    private function getSequence(\MongoCollection $coll, $key)
    {
        $next = $coll->findAndModify(
            ["_id" => $key],
            ['$inc' => ["seq" => 1]],
            null,
            ['new' => true]
        );
        if (isset($next['seq'])) {
            return $next['seq'];
        } else {
            return 0;
        }
    }
}
