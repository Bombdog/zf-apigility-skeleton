<?php

namespace OdmScope\Scope;

/**
 * ScopeSet is a collection of scope objects.
 *
 * Class ScopeSet
 * @package OdmScope\Scope
 */
class ScopeSet implements \ArrayAccess
{
    /**
     * Array of scopes
     * @var array
     */
    protected $scopes;

    /**
     * ScopeSet constructor.
     *
     * @param array $scopes
     */
    public function __construct(array $scopes = [])
    {
        foreach ($scopes as $scope) {
            if(!$scope instanceof $scope) {
                throw new \InvalidArgumentException("Scope must be an instanmce of scope");
            }
        }

        $this->scopes = $scopes;
    }

    /**
     * The number of scopes in the set
     */
    public function count()
    {
        return count($this->scopes);
    }

    /**
     * Add a new scope to the set
     *
     * @param Scope $scope
     *
     * @return $this
     */
    public function addScope(Scope $scope)
    {
        if(!$this->contains($scope)) {
            $this->scopes[] = $scope;
        }

        return $this;
    }

    /**
     * Find if a scope is already in the set.
     *
     * @param Scope $scope
     *
     * @return bool
     */
    public function contains(Scope $scope)
    {
        /** @var Scope $scope */
        foreach ($this->scopes as $match) {
            if($match->isIdenticalTo($scope)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return (is_int($offset) && $offset > -1 && $offset < $this->count());
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if($this->offsetExists($offset)) {
            return $this->scopes[$offset];
        }

        return null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param int $offset
     * @param Scope $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if($value instanceof Scope) {
            if($offset === null) {
                $this->addScope($value);
            } elseif (is_int($offset) && $offset > -1 && $offset <= $this->count()) {
                if(!$this->contains($value)) {
                    $this->scopes[$offset] = $value;
                }
            }
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        // noop
    }
}
