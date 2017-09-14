<?php

namespace Entity\Document\Traits;

use Entity\EntityInvalidArgumentException;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * An automatic sequence for your entity.
 * @package Entity\Document\Traits
 */
trait SequenceTrait
{
    /**
     * Integer sequence
     * @var int
     * @ODM\Field(type="int")
     */
    protected $sequence;

    /**
     * Get the sequence.
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set the sequence. This is not for general use and will be called on the prePersist
     * event. To stop misuse by eager developers it will throw an exception if any attempt
     * is made to modify the sequence value.
     * @param int $value
     * @return $this
     */
    public function setSequence($value)
    {
        if ($this->sequence === null) {
            $this->sequence = (int) $value;
        } else {
            throw new EntityInvalidArgumentException("Sequences cannot be modified once set.");
        }

        return $this;
    }
}
