<?php

namespace OdmAuth\Request;


interface PagedQueryInterface
{
    /**
     * Set the page size
     *
     * @param int $pageSize
     *
     * @return mixed
     */
    public function setPageSize($pageSize);

    /**
     * Get the page size
     *
     * @return int
     */
    public function getPageSize();

    /**
     * Set the page
     *
     * @param int $page
     *
     * @return mixed
     */
    public function setPage($page);

    /**
     * @return int
     */
    public function getPage();

    /**
     * An array of terms to be used for filtering
     * @return mixed
     */
    public function getFilter();

    /**
     * An an array of fields and ordering for each field
     * @return array
     */
    public function getSort();

    /**
     * An array of fields to be used in a select
     * @return array
     */
    public function getFields();

    /**
     * Set the filter terms
     * @param array $filter
     *
     * @return $this
     */
    public function setFilter(array $filter);

    /**
     * Set the sort fields
     *
     * @param mixed $sort
     *
     * @return $this
     */
    public function setSort(array $sort);

    /**
     * Set the fields used for select.
     * @param mixed $fields
     * @return $this
     */
    public function setFields(array $fields);

    /**
     * Named preset filter/sort
     * @return string
     */
    public function getPreset();

    /**
     * @param string $preset
     *
     * @return $this
     */
    public function setPreset($preset);
}
