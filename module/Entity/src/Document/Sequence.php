<?php

namespace Entity\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * This is a placeholder entity for mapping to the sequence collection.
 * Do not use this entity.
 * @ODM\Document(collection="seq")
 */
class Sequence
{
    /**
     * @ODM\Id
     * @var string
     **/
    public $id;

    /**
     * @ODM\Field(type="int")
     * @var int
     */
    public $seq;
}
