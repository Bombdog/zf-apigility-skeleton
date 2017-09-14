<?php
namespace Entity\Document\Traits;

use Entity\Util\UTC;
use Entity\EntityInvalidArgumentException;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * An automatic timestamp for updatedAt.
 * Class UpdatedStampTrait
 * @package Entity\Document\Traits
 */
trait UpdatedStampTrait
{
    /**
     * Utc time of update
     * @var \Datetime
     * @ODM\Field(type="utcdatetime")
     */
    protected $updatedAt;

    /**
     * Get the last time the record was updated
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set the time updatedAt. Generally you will pass null.
     * @param \Datetime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        if($updatedAt === null) {
            $updatedAt = UTC::createUTCDate();
        }
        else if(!$updatedAt instanceof \DateTime || !UTC::isUtcDate($updatedAt)) {
            throw new EntityInvalidArgumentException("updatedAt must be a UTC Date.");
        }
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
