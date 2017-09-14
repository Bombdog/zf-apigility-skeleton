<?php
namespace Entity\Document\Traits;

use Entity\EntityInvalidArgumentException;
use Entity\Util\UTC;


/**
 * This trait is to be used when a record is marked as deleted, NOT actually deleted from the DB ;-)
 * NB. this trait is not used in the ODM Lifecycle.
 * Class DeletedStampTrait
 * @package Entity\Document\Traits
 */
trait DeletedStampTrait
{
    /**
     * Utc time of deletion
     * @var \Datetime
     * @ODM\Field(type="utcdatetime")
     */
    protected $deletedAt;

    /**
     * Get the deletion time.
     * @return \Datetime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set the time deletedAt.
     * Generally you will pass 'now' which time stamps the record.
     * Note that you need to call undelete to remove a record from trash.
     * @param \Datetime $deletedAt
     * @return $this
     */
    public function setDeletedAt($deletedAt)
    {
        if($deletedAt === 'now') {
            $deletedAt = UTC::createUTCDate('now');
        }
        else if(!$deletedAt instanceof \DateTime || !UTC::isUtcDate($deletedAt)) {
            throw new EntityInvalidArgumentException("deletedAt must be a UTC Date.");
        }
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Restore the record from trash, i.e. sets deletedAt stamp to null.
     * @return $this
     */
    public function unDelete()
    {
        $this->deletedAt = null;
        return $this;
    }

    /**
     * Convenience to test if a record is deleted.
     * @return bool
     */
    public function isDeleted()
    {
        return ($this->deletedAt !== null);
    }
}
