<?php

namespace Entity\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * An entity for keeping track of tags for news.
 * NB this entity is not embedded, is simply for keeping tags in a collection of their own with a unique index.
 *
 * @ODM\Document(
 *     collection="articletags",
 *     repositoryClass="Entity\Repository\ArticleTagRepository"
 * )
 * @ODM\Indexes({
 *   @ODM\Index(keys={"title"="asc"},unique=true,name="idxArticleTag")
 * })
 */
class ArticleTag
{
    /**
     * PK
     * @ODM\Id
     * @var string
     **/
    protected $id;

    /**
     * Title of the tag.
     * @var string
     * @ODM\Field(type="string")
     */
    protected $title;

    /**
     * Get id
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the title for the tag
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title for the tag. Must be unique.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
}
