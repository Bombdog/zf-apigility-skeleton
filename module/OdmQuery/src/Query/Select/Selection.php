<?php

namespace OdmQuery\Query\Select;

/**
 * Select list for running a query.
 * Also supports blacklisted and default fields.
 */
class Selection
{
    /**
     * Array of primary fields
     * @var array
     */
    protected $primary = [];

    /**
     * 2D array of secondary fields
     * @var array
     */
    protected $secondary = [];

    /**
     * Select all flag indicates that there should be no restrictions.
     * Functionally equivalent to SQL "SELECT *"
     * @var bool
     */
    protected $selectAll = false;

    /**
     * Defaults must be set before blacklist.
     * @var bool
     */
    protected $defaultsSet = false;

    /**
     * Selection constructor.
     *
     * @param array|null $requestFields
     */
    public function __construct(array $requestFields = null)
    {
        if (!empty($requestFields)) {

            # All fields can be requested i.e. "?fields=all", so long as there isn't a blacklist
            if (count($requestFields) == 1 && $requestFields[0] == 'all') {
                $this->selectAll = true;
                return;
            }

            $primaryFields = [];
            $nestedFields = [];

            # Split primary and secondary (nested) field lists
            foreach ($requestFields as $field) {
                $colon = strpos($field, ':');
                if ($colon) {
                    $primary = substr($field, 0, $colon);
                    $nested = substr($field, $colon + 1);
                    $primaryFields[$primary] = true;
                    $nestedFields[$primary][] = $nested;
                } else {
                    $primaryFields[$field] = true;
                }
            }

            $primaryFields = array_keys($primaryFields);
            $this->primary = $primaryFields;
            $this->secondary = $nestedFields;
        }
    }

    /**
     * Apply any defaults.
     * Defaults can only be set if there is no selection (and no "select all")
     *
     * @param array $defaults
     *
     * @return $this
     */
    public function setDefaults(array $defaults)
    {
        $this->defaultsSet = true;
        if (!empty($this->getPrimary()) && !empty($defaults) && !$this->selectAll) {
            $this->primary = $defaults;
        }

        return $this;
    }

    /**
     * Set a blacklist to hide fields in restricted scopes
     *
     * @param array $blacklist
     *
     * @return $this
     * @throws \Exception
     */
    public function setBlacklist(array $blacklist)
    {
        if (empty($blacklist)) {
            return $this;
        }

        if (!$this->defaultsSet || empty($this->primary)) {
            throw new \Exception('Blacklist may only be set with defaults');
        }

        # Apply the blacklist over the primary view
        $this->primary = array_diff($this->primary, $blacklist);
        if (empty($this->primary)) {
            # IMPORTANT: empty array implies ALL fields are visible, but NONE are so an exception MUST BE thrown
            throw new \Exception("A blacklist has excluded all requested fields, nothing to show.");
        }

        # Apply the blacklist to any nested secondary view (which is keyed by primary)
        if (!empty($this->secondary)) {
            $this->secondary = array_diff_key($this->secondary, array_flip($blacklist));
        }

        return $this;
    }

    /**
     * Get the primary fields (or empty array means select *)
     *
     * @return array
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * Get secondary fields (for embedded documents)
     * eg. address:line1, address:postcode
     *
     * @return array
     */
    public function getSecondary()
    {
        return $this->secondary;
    }

}