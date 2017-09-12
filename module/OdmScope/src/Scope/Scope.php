<?php

namespace OdmScope\Scope;

use Entity\EntityInvalidArgumentException;

/**
 * Scope class with a basic read / write / write_all hierarchy.
 *
 * other > write_all > write > read
 *
 */
class Scope
{
    const SUBTYPE_READ = 'read';
    const SUBTYPE_WRITE = 'write';
    const SUBTYPE_WRITE_ALL = 'write_all';
    const SUBTYPE_OTHER = 'other';

    /**
     * Type of scope, eg article, event, invoice, whatever
     * @var string
     */
    protected $type;

    /**
     * Parsed classification (hierarchical) of subtype
     * @var string
     */
    protected $subType;

    /**
     * Actual subtype
     * @var string
     */
    protected $subTypeActual;

    /**
     * Scope constructor.
     *
     * @param string $scope
     */
    public function __construct($scope)
    {
        if (!is_string($scope) || strlen($scope) > 50) {
            throw new EntityInvalidArgumentException('Invalid scope');
        }

        $parts = explode(':', trim(strtolower($scope)));

        if (count($parts) == 2) {
            $this->type = trim($parts[0]);
            $this->subTypeActual = trim($parts[1]);
            if (in_array($this->subTypeActual, [self::SUBTYPE_READ, self::SUBTYPE_WRITE, self::SUBTYPE_WRITE_ALL])) {
                $this->subType = $this->subTypeActual;
            } else {
                $this->subType = self::SUBTYPE_OTHER;
            }
        }

        if (empty($this->type) || empty($this->subTypeActual)) {
            throw new EntityInvalidArgumentException('Scope must be in the form type:subtype');
        }
    }

    /**
     * Does the type (namespace of the scope) match?
     *
     * @param Scope $scope
     *
     * @return bool
     */
    public function isTypeMatch(Scope $scope)
    {
        return $this->type == $scope->getType();
    }

    /**
     * Is this scope an escalation of another scope?
     * Compares type and weight to decide the answer.
     * In the case where two "other" subtypes are compared then for security reasons it's assumed to be an escalation.
     *
     * @param Scope $scope
     *
     * @return bool
     */
    public function isEscalationOf(Scope $scope)
    {
        # cannot compare apples and oranges
        if (!$this->isTypeMatch($scope)) {
            return false;
        }

        # an actual sub-type match is never an escalation (identical scope)
        if($scope->getSubTypeActual() == $this->getSubTypeActual()) {
            return false;
        }

        return $this->getSubTypeWeight(true) > $scope->getSubTypeWeight();
    }

    /**
     * Get the type (namespace)
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the (parsed) sub type
     * @return string
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * Get the actual subtype
     * @return string
     */
    public function getSubTypeActual()
    {
        return $this->subTypeActual;
    }

    /**
     * Get a weighting for the subtype.
     * If the subtype is "other" then weight is set to maximum.
     * If subtype is "other" then setting local=true adds +1.
     * This way "other" is always an privilege escalation of "other", this is for security purposes, there is
     * no heirarchy for "other" i.e custom subtypes.
     *
     * @param bool $local
     *
     * @return int
     */
    public function getSubTypeWeight($local = false)
    {
        $weights = [self::SUBTYPE_READ, self::SUBTYPE_WRITE, self::SUBTYPE_WRITE_ALL];
        $weight = array_search($this->subType, $weights, true);
        if ($weight === false) {
            $weight = count($weights);
            if($local) {
                $weight += 1;
            }
        }

        return $weight;
    }

    /**
     * Hydrate the original scope as string
     * @return string
     */
    public function __toString()
    {
        return $this->getType() . ':' . $this->getSubTypeActual();
    }
}
