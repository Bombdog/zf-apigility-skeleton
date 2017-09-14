<?php

namespace OdmQuery\Query;

use OdmAuth\Request\PagedQueryInterface;

/**
 *
 * @package OdmQuery\Query
 */
class ApiQuery implements PagedQueryInterface
{
    /**
     * The the requested page number
     * Default is 1.
     * @var int
     */
    protected $page = 1;

    /**
     * The requested page size.
     * Default is 25. @todo: make configurable
     * @var int
     */
    protected $pageSize = 25;

    /**
     * Filter terms
     * @var array
     */
    protected $filter = [];

    /**
     * Sort order
     * @var array
     */
    protected $sort = [];

    /**
     * Fields for selection
     * @var array
     */
    protected $fields = [];

    /**
     * A named preset in the request.
     * @var string
     */
    protected $preset;


    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * An array of terms to be used for filtering
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * An an array of fields and ordering for each field
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * An array of fields to be used in a select
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param int $pageSize
     *
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $pageSize = intval($pageSize);
        if ($pageSize > 0) {
            $this->pageSize = $pageSize;
        }
        return $this;
    }

    /**
     * @param int $page
     *
     * @return $this
     */
    public function setPage($page)
    {
        $page = intval($page);
        if ($page > 0) {
            $this->page = $page;
        }
        return $this;
    }

    /**
     * Set the filter terms
     *
     * @param array $filter
     *
     * @return $this
     */
    public function setFilter(array $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Set the sort fields
     *
     * @param mixed $sort
     *
     * @return $this
     */
    public function setSort(array $sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Set the fields used for select.
     *
     * @param mixed $fields
     *
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Named preset filter/sort
     *
     * @return string
     */
    public function getPreset()
    {
        return $this->preset;
    }

    /**
     * @param string $preset
     *
     * @return $this
     */
    public function setPreset($preset)
    {
        $this->preset = $preset;
        return $this;
    }
}
