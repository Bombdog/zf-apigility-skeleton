<?php
namespace Entity\Document\Traits;

use Entity\Util\Slug;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Entity\EntityInvalidArgumentException;

/**
 * Tags trait, a way to implement taggable interface
 * @package Entity\Document\Traits
 */
trait TagsTrait
{
    /**
     * Array of tags
     * @var array
     * @ODM\Field(type="collection")
     */
    protected $tags;

    /**
     * Set tags. Using more or less the same scheme as slashdot.
     *
     * @see http://stackoverflow.com/questions/13882516/content-tagging-with-mongodb
     * @see https://slashdot.org/faq/tags.shtml
     *
     * @param array $tags
     *
     * @return $this
     */
    public function setTags($tags)
    {
        if ($tags !== null) {

            if (!is_array($tags)) {
                throw new EntityInvalidArgumentException('Tags must be passed in an array (or null)');
            }

            $count = count($tags);
            for ($i = 0; $i < $count; $i++) {
                $tag = trim(strtolower($tags[$i]));
                if (!Slug::isValid($tag)) {
                    throw new EntityInvalidArgumentException($tag . ' is not a valid tag');
                }
                $tags[$i] = $tag;
            }

            array_unique($tags);
            $this->tags = $tags;
        } else {
            $this->tags = null;
        }

        return $this;
    }

    /**
     * Get tags.
     *
     * @return array $tags
     */
    public function getTags()
    {
        return $this->tags;
    }

}

