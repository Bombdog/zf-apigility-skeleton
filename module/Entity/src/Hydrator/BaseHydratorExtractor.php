<?php

namespace Entity\Hydrator;

use Entity\Util\UTC;
use Entity\Document\Traits\AuthorTrait;
use Entity\Filter\MethodsMatchFilter;
use Phpro\DoctrineHydrationModule\Hydrator\ODM\MongoDB\DoctrineObject;
use Phpro\DoctrineHydrationModule\Hydrator\ODM\MongoDB\Strategy\DateTimeField;
use Zend\Hydrator\Filter\FilterComposite;
use Zend\Paginator\Paginator;

/**
 * Default extractor for extracting results from doctrine objects.
 * This can be used as is or subclassed.
 *
 * Based on the DoctrineObject hydrator provided by Phpro\DoctrineHydrationModule
 *
 */
class BaseHydratorExtractor extends DoctrineObject
{
    const TRAIT_AUTHOR = 'Entity\Document\Traits\AuthorTrait';

    /**
     * Array of the primary (top level) fields in the view.
     * @var array
     */
    protected $primaryView;

    /**
     * 2D Array of the secondary (embedded) fields, keyed by primary.
     * @var array
     */
    protected $nestedView;

    /**
     * Fields in an existing record that cannot be edited.
     * @var array
     */
    protected $readOnlyFields;

    /**
     * Add custom strategies to specific field types
     * Overrides the phpro version to account for our utcdatetime custom type
     */
    protected function prepareFieldStrategies()
    {
        $fields = $this->metadata->getFieldNames();
        foreach ($fields as $field) {
            if ($this->hasStrategy($field) || in_array($field, $this->metadata->getAssociationNames())) {
                continue;
            }

            $fieldMeta = $this->metadata->fieldMappings[$field];
            switch ($fieldMeta['type']) {
                case 'date':
                case 'utcdatetime':
                    $this->addStrategy($field, new DateTimeField(false));
                    break;
                case 'timestamp':
                    $this->addStrategy($field, new DateTimeField(false));
                    break;
            }
        }
    }

    /**
     * Set the fields to be viewed. May be split into "primary" and "nested" views.
     * By default all fields are viewable so this creates a restriction on what can be returned.
     * @param array $fields
     */
    public function setViewableFields(array $fields)
    {
        $this->primaryView = (isset($fields['primary'])) ? $fields['primary'] : $fields;
        if(isset($fields['nested'])) {
            $this->nestedView = [];
            foreach($fields['nested'] as $key => $view) {
                $this->nestedView[$key] = array_flip($view);
            }
        }

        if( $this->hasFilter(__FUNCTION__) ) {
            $this->removeFilter(__FUNCTION__);
        }

        if(!empty($this->primaryView)) {
            $this->addFilter(
                __FUNCTION__,
                new MethodsMatchFilter($this->primaryView, false),
                FilterComposite::CONDITION_AND
            );
        }
    }

    /**
     * An optional array of fields that are read-only and should be ignored when hydrating
     * an *existing* object.
     * @param array $readOnlyFields
     */
    public function setReadOnlyFields(array $readOnlyFields)
    {
        if( !empty($readOnlyFields) ) {
            $this->readOnlyFields = array_flip($readOnlyFields);
        }
    }

    /**
     * Reset the viewable fields. Clears the primary and nested views.
     */
    public function resetView()
    {
        $this->primaryView = null;
        $this->nestedView = null;
        $this->removeFilter('setViewableFields');
    }

    /**
     * The extract function, adapted to return null when null is passed.
     * Also applies the nested view that filters fields from embedded objects.
     * @param object $object
     * @return array|null
     */
    public function extract($object)
    {
        if(empty($object)) {
            return null; // prevents a "class not found in chain of hydrators" exception
        }
        else {
            return parent::extract($object);
        }
    }

    /**
     * Converts a value for extraction. If no strategy exists the plain value is returned.
     * @param  string $name  The name of the strategy to use.
     * @param  mixed  $value  The value that should be converted.
     * @param  mixed  $object The object is optionally provided as context.
     * @return mixed
     */
    public function extractValue($name, $value, $object = null)
    {
        if ($value !== null && $this->hasStrategy($name)) {
            $strategy = $this->getStrategy($name);
            $value = $strategy->extract($value, $object);
            if(isset( $this->nestedView[$name] ) && is_array($value)) {
                $strategyClass = get_class($strategy);
                if(strpos($strategyClass,'Collection')) {
                    $value = $this->applyNestedViewToCollection($value,$this->nestedView[$name]);
                }
                else {
                    $value = $this->applyNestedViewToItem($value,$this->nestedView[$name]);
                }
            }
        }
        return $value;
    }

    /**
     * Extract a collection for returning as a json response.
     * An optional parameter allows you to switch metatdata (page size, count etc) on or off.
     * @param Paginator $collection
     * @param bool $metas
     * @return array
     */
    public function extractCollection(Paginator $collection, $metas=true)
    {
        $data = [];
        $result = $collection->getCurrentItems();

        foreach($result as $object) {
            $data[] = $this->extract($object);
        }

        if($metas) {
            $total = $collection->getTotalItemCount();
            $pageSize = $collection->getItemCountPerPage();
            $pageCount = ceil($total/$pageSize);
            $page = $collection->getCurrentPageNumber();
            $hydrated = [
                'page'      => $page,
                'pageSize'  => $pageSize,
                'total'     => $total,
                'pageCount' => $pageCount,
                'count'     => count($data),
                'data'      => &$data
            ];
            return $hydrated;
        }
        else {
            return $data;
        }
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array  $data
     * @param  object $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        if($object->getId() !== null && $this->readOnlyFields !== null) {
            $data = array_diff_key($data,$this->readOnlyFields);
        }

        return parent::hydrate($data,$object);
    }

    /**
     * Hydrate an object, stamping the object with the sequence
     * of the user making the change.
     *
     * @param array $data
     * @param $object
     * @param int $userId
     *
     * @return object
     */
    public function hydrateWithContext(array $data, $object, $userId)
    {
        $object = $this->hydrate($data,$object);

        # Set the Author trait if present
        $traits = class_uses($object);
        if( isset($traits[self::TRAIT_AUTHOR]) ) {
            if($object->getId() === null) {
                /** @var AuthorTrait $object */
                $object->setCreatedBy($userId);
            }
            $object->setUpdatedBy($userId);
        }
        return $object;
    }

    /**
     * Apply a nested view to a collection of embedded objects.
     * @param array $collection
     * @param array $view
     * @return array
     */
    private function applyNestedViewToCollection(array $collection, array $view)
    {
        $count = count($collection);
        for($i=0; $i<$count; $i++) {
            $collection[$i] = $this->applyNestedViewToItem($collection[$i], $view);
        }
        return $collection;
    }

    /**
     * Apply a nested view to an embedded object.
     * @param array $item
     * @param array $view
     * @return array
     */
    private function applyNestedViewToItem(array $item, array $view)
    {
        return array_intersect_key($item,$view);
    }

    /**
     * Handle various type conversions that should be supported natively by Doctrine (like DateTime)
     * @param  mixed  $value
     * @param  string $typeOfField
     * @return mixed
     */
    protected function handleTypeConversions($value, $typeOfField)
    {
        switch ($typeOfField) {
            case 'boolean':
                if ($value === 'false') {
                    $value = false;
                }
                break;

            case 'utcdatetime':
                $value = UTC::dateFromTimestamp($value);
                break;

            case 'datetimetz':
            case 'datetime':
            case 'time':
            case 'date':
                if ('' === $value) {
                    return null;
                }

                if (is_int($value)) {
                    $dateTime = new \DateTime();
                    $dateTime->setTimestamp($value);
                    $value = $dateTime;
                } elseif (is_string($value)) {
                    $value = new \DateTime($value);
                }

                break;
            default:
        }

        return $value;
    }
}
