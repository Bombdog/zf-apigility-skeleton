<?php

namespace Entity\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Entity\Util\UTC;
use Entity\Document\Traits\CreatedStampTrait;
use Entity\Document\Traits\DeletedStampTrait;
use Entity\Document\Traits\SequenceTrait;
use Entity\Document\Traits\TagsTrait;
use Entity\Document\Traits\UpdatedStampTrait;
use Entity\EntityInvalidArgumentException;
use Doctrine\Common\Collections\Collection;

/**
 * An entity for a news article.
 * Articles have a mongo multikey index for tags and a full text search capability on title/content.
 *
 * @ODM\Document(
 *     collection="articles",
 *     repositoryClass="Entity\Repository\ArticleRepository"
 * )
 * @ODM\Indexes({
 *   @ODM\Index(keys={"sequence"="desc"},unique=true,name="idxArticleSequence"),
 *   @ODM\Index(keys={"tags"="desc"},name="idxArticleTags"),
 *   @ODM\Index(keys={"title"="text","content"="text"},name="idxArticleSearch")
 * })
 */
class Article
{
    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;
    const STATUS_DELETED = 3;

    use TagsTrait;
    use CreatedStampTrait;
    use UpdatedStampTrait;
    use DeletedStampTrait {
        setDeletedAt as protected setDeletedAtTrait;
    }
    use SequenceTrait;

    /**
     * @ODM\Id
     * @var $id
     **/
    protected $id;

    /**
     * Integer status, eg draft/published
     * @var int
     * @ODM\Field(type="int")
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * Utc time for publication
     * @var \Datetime
     * @ODM\Field(type="utcdatetime")
     */
    protected $publishedAt;

    /**
     * Title of the article.
     * @var string
     * @ODM\Field(type="string")
     */
    protected $title;

    /**
     * Name of an author for the article.
     * @var string
     * @ODM\Field(type="string")
     */
    protected $author;

    /**
     * HTML Content
     * @var string
     * @ODM\Field(type="string")
     */
    protected $content;

    /**
     * File names of image(s) attached to the article.
     * @var Collection
     * @ODM\Field(type="collection")
     */
    protected $images;


    /**
     * Get id
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set status
     *
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $min = self::STATUS_DRAFT;
        $max = self::STATUS_DELETED;

        $status = (int) $status;
        if ($status < $min || $status > $max) {
            throw new EntityInvalidArgumentException("Status argument is invalid.");
        }

        if ($status != $this->status) {
            if ($status == self::STATUS_DELETED) {
                $this->setDeletedAt('now');
            } else {
                $this->deletedAt = null;
                $this->status = $status;
            }
        }

        return $this;
    }

    /**
     * Get status
     * @return int $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the last time the record was updated
     * @return \Datetime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set the publishedAt time. Can be in the future.
     *
     * @param \Datetime $publishedAt
     *
     * @return $this
     */
    public function setPublishedAt($publishedAt)
    {
        if ($publishedAt === null) {
            $publishedAt = UTC::createUTCDate();
        } else {
            if (!$publishedAt instanceof \DateTime || !UTC::isUtcDate($publishedAt)) {
                throw new EntityInvalidArgumentException("publishedAt must be a UTC Date.");
            }
        }
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * Set title
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

    /**
     * Get title
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Set author
     *
     * @param string $author
     *
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Get author
     *
     * @return string $author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set article content
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get article content
     *
     * @return string $content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set images. Dubious collection of images files.
     *
     * @param Collection $images
     *
     * @return $this
     */
    public function setImages($images)
    {
        $this->images = $images;
        return $this;
    }

    /**
     * Get images.
     * @return Collection $images
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Place article in trash.
     *
     * @param $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt($deletedAt)
    {
        $this->setDeletedAtTrait($deletedAt);
        $this->status = self::STATUS_DELETED;

        return $this;
    }

    /**
     * Remove from trash
     *
     * @return $this
     */
    public function undelete()
    {
        $this->deletedAt = null;
        $this->status = self::STATUS_DRAFT;

        return $this;
    }
}