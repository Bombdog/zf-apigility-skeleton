<?php
namespace Entity\Document\Traits;

use Entity\Util\UTC;
use Entity\EntityInvalidArgumentException;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * An automatic timestamp for createdAt.
 * Class CreateStampTrait
 * @package Entity\Document\Traits
 */
trait CreatedStampTrait
{
    /**
     * Utc time of creation, ie creation of this object on the server.
     * @var \Datetime
     * @ODM\Field(type="utcdatetime")
     */
    protected $createdAt;

    /**
     * Get the time the record was created at
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the time createdAt. Generally you will pass null.
     * @param \Datetime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        if($createdAt === null) {
            $createdAt = UTC::createUTCDate();
        }
        else if(!$createdAt instanceof \DateTime || !UTC::isUtcDate($createdAt)) {
            throw new EntityInvalidArgumentException("createdAt must be a UTC Date.");
        }
        $this->createdAt = $createdAt;
        return $this;
    }
}
