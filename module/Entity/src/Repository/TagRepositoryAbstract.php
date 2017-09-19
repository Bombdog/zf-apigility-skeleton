<?php

namespace Entity\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class ArticleTagRepository
 * @package Entity\Repository
 */
abstract class TagRepositoryAbstract extends BaseRepository
{
    /**
     * A bulk insert of tags directly into the tags collection.
     * Duplicate keys are ignored.
     * (NB duplicate key exception appears after complete batch insert using "continueOnError" flag)
     *
     * @param array $tags
     */
    public function addTags(array $tags)
    {
        /** @var DocumentManager $dm */
        $dm = $this->getDocumentManager();

        $tagData = [];
        foreach ($tags as $tag) {
            $tagData[] = ['title'=>$tag];
        }

        if(count($tagData)) {
            $collection = $dm->getDocumentCollection($this->getDocumentName())->getMongoCollection();
            try {
                $collection->batchInsert(
                    $tagData,
                    ['continueOnError' => true]
                );
            }
            catch (\MongoDuplicateKeyException $e) {};
        }
    }

    /**
     * Get the tags from mongo collection
     * This could be updated later to allow an argument to filter results.
     * Returns tags in alphabetical order.
     *
     * @return array
     */
    public function getTags()
    {
        $qb = $this->createQueryBuilder();
        $qb->hydrate(false)->select('title')->sort('title',1);
        $cursor = $qb->getQuery()->execute();

        $tags = [];
        foreach ($cursor as $row) {
            $tags[] = $row['title'];
        }

        return $tags;
    }
}
