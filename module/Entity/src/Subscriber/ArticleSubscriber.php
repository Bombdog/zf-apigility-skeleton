<?php
namespace Entity\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Entity\Document\Article;
use Entity\Repository\ArticleTagRepository;

/**
 * Article Subscriber
 *
 * When tags are created for news they need to be kept for future reference, e.g. for autocomplete.
 * Therefore any changes to articles are automatically passed into the article tags collection.
 *
 */
class ArticleSubscriber implements EventSubscriber
{
    /**
     * Local stash of tags for a bulk insert
     * @var array
     */
    private $tags = [];

    /**
     * Return an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postFlush
        ];
    }

    /**
     * Any new news articles should be automatically checked for new tags.
     * Tags are buffered here not flushed.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->stashTags($args->getObject());
    }

    /**
     * Any updated articles should be automatically checked for new tags.
     * Tags are buffered here not flushed.
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->stashTags($args->getObject());
    }

    /**
     * After new or existing articles are flushed any new tags in the buffer are flushed also.
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        /** @var ArticleTagRepository $repo */
        $repo = $dm->getRepository('Entity\Document\ArticleTag');
        $repo->addTags($this->tags);
        $this->tags = [];
    }

    /**
     * Store the tags from an update or insert
     *
     * @param $entity
     */
    private function stashTags($entity)
    {
        if ($entity instanceof Article) {
            $tags = $entity->getTags();
            if (!empty($tags)) {
                $this->tags = array_merge($this->tags, $tags);
            }
        }
    }
}
