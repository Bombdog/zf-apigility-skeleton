<?php
namespace Entity\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Entity\Document\Traits\CreatedStampTrait;
use Entity\Document\Traits\DeletedStampTrait;
use Entity\Document\Traits\UpdatedStampTrait;

/**
 * Timestamp Subscriber
 * This class allows us to stamp entities on-the-fly.
 */
class TimestampSubscriber implements EventSubscriber
{
    const TRAIT_CREATED = CreatedStampTrait::class;
    const TRAIT_UPDATED = UpdatedStampTrait::class;
    const TRAIT_DELETED = DeletedStampTrait::class;

    /**
     * Return an array of events this subscriber wants to listen to.
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate
        ];
    }

    /**
     * New entities are both created and updated at the same time.
     * We share the created date so the two are identical when the object is created.
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $traits = class_uses($entity);

        $createdAt = null;
        if(isset($traits[self::TRAIT_CREATED])) {
            $entity->setCreatedAt(null);
            $createdAt = $entity->getCreatedAt();
        }

        if(isset($traits[self::TRAIT_UPDATED])) {
            $entity->setUpdatedAt($createdAt);
        }
    }

    /**
     * Existing entities must have the UpdatedAt time changed if they are updated.
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        /** @var UpdatedStampTrait $entity */
        $entity = $args->getObject();
        $traits = class_uses($entity);
        if(isset($traits[self::TRAIT_UPDATED])) {
            $dm = $args->getDocumentManager();
            $class = $dm->getClassMetadata(get_class($entity));
            $uow = $dm->getUnitOfWork();
            $changeset = $uow->getDocumentChangeSet($entity);
            if(!empty($changeset)) {
                $entity->setUpdatedAt(null);
                $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($class, $entity);
            }
        }
    }
}
