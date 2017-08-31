<?php
namespace Entity\Document\Traits;

use Entity\Document\User;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * An automatic marker for creator/editor
 * @package Entity\Document\Traits
 */
trait AuthorTrait
{
    /**
     * Original author of the object.
     * @var int
     * @ODM\Field(type="int")
     */
    protected $createdBy;

    /**
     * Last person to update the object.
     * @var int
     * @ODM\Field(type="int")
     */
    protected $updatedBy;

    /**
     * Get createdby.
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdby.
     * @param int $createdBy
     * @return $this
     */
    public function setCreatedBy($createdBy)
    {
        if($createdBy instanceof User) {
            $createdBy = $createdBy->getSequence();
        }
        if($this->createdBy === null) {
            $this->createdBy = (int)$createdBy;
        }
        return $this;
    }

    /**
     * Get updatedby.
     * @return int
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set updatedby.
     * @param int $updatedBy
     * @return $this
     */
    public function setUpdatedBy($updatedBy)
    {
        if($updatedBy instanceof User) {
            $updatedBy = $updatedBy->getSequence();
        }
        $this->updatedBy = (int) $updatedBy;
        return $this;
    }
}
