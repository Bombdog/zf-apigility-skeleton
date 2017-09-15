<?php

namespace OdmQuery\Query\Select;

/**
 * A view on
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
     * Selection constructor.
     *
     * @param array|null $requestFields
     */
    public function __construct(array $requestFields = null)
    {
        if(!empty($requestedFields)) {
            # All fields can be requested i.e. "?fields=all", so long as there isn't a blacklist
            if(count($requestedFields) == 1 && $requestedFields[0] == 'all') {
                $this->selectAll = true;
                return;
            }

            $primaryFields = [];
            $nestedFields = [];

            # Split primary and secondary (nested) field lists
            foreach ($requestFields as $field) {
                $colon = strpos($field,':');
                if($colon) {
                    $primary = substr($field,0,$colon);
                    $nested = substr($field,$colon+1);
                    $primaryFields[$primary] = true;
                    $nestedFields[$primary][] = $nested;
                }
                else {
                    $primaryFields[$field] = true;
                }
            }

            $primaryFields = array_keys($primaryFields);
            $this->primary = $primaryFields;
            $this->secondary = $nestedFields;
        }
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