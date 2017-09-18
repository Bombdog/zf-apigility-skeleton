<?php

namespace OdmScope\Scope;

/**
 * ScopeSet is a collection of scope objects.
 *
 * Class ScopeSet
 * @package OdmScope\Scope
 */
class ScopeSet implements \Iterator
{
    /**
     * Array of scopes
     * @var array
     */
    protected $scopes;

    /**
     * Array of matches (of above scopes that have been matched)
     * @var array
     */
    protected $matches = [];

    /**
     * Flag array end to external iterator
     * @var bool
     */
    protected $end = false;

    /**
     * ScopeSet constructor.
     *
     * @param array $scopes
     */
    public function __construct(array $scopes = [])
    {
        foreach ($scopes as $key => $scope) {
            if(!$scope instanceof Scope) {
                throw new \InvalidArgumentException("Scope must be an instance of scope");
            }
        }

        $this->scopes = array_values($scopes);
        reset($this->scopes);
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
     * Same as contains but stores matches for later reference
     *
     * @param Scope $scope
     *
     * @return bool
     */
    public function matches(Scope $scope)
    {
        if($result = $this->contains($scope)) {
            $this->matches[] = $scope;
        }

        return $result;
    }

    /**
     * Get the previous scope matches for this set.
     *
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->scopes);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $next = next($this->scopes);
        if($next === false) {
            $this->end = true;
        }
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->scopes);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        if($this->end) {
            return false;
        }

        $count = $this->count();

        return $count > 0 && $this->key() < $count;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->scopes);
        $this->end = false;
    }
}
