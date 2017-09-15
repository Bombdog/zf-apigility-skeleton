<?php

namespace OdmScope\Scope;

use OdmAuth\Request\TargetedScopeInterface;
use OdmScope\DeniedScopeException;


class TargetScope implements TargetedScopeInterface
{
    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var ScopeSet
     */
    protected $readScope;

    /**
     * @var ScopeSet
     */
    protected $writeScope;

    /**
     * @var ScopeSet
     */
    protected $writeAllScope;

    /**
     * TargetScope constructor.
     *
     * An optional scope name can be set at construct
     *
     * @param string $scopeName
     */
    public function __construct($scopeName = null)
    {
        if ($scopeName !== null && is_string($scopeName)) {
            $read = new Scope($scopeName . ':read');
            $write = new Scope($scopeName . ':write');
            $writeAll = new Scope($scopeName . ':write_all');
            $this->readScope = new ScopeSet([$read, $write, $writeAll]);
            $this->writeScope = new ScopeSet([$write, $writeAll]);
            $this->writeAllScope = new ScopeSet([$writeAll]);
        }
    }

    /**
     * Set the name of the route (for reference)
     *
     * @param string $routeName
     *
     * @return $this
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * Set a read scope or scopes that are allowed to read from the resource
     *
     * @param array $readScope
     *
     * @return $this
     */
    public function setReadScope(array $readScope)
    {
        if(!empty($readScope)) {
            $this->readScope = $this->parseScopeArray($readScope);
        }

        return $this;
    }

    /**
     * Set a write scope or scopes that are allowed to write to the resource
     *
     * @param array $writeScope
     *
     * @return $this
     */
    public function setWriteScope(array $writeScope)
    {
        if(!empty($writeScope)) {
            $this->writeScope = $this->parseScopeArray($writeScope);
        }

        return $this;
    }

    /**
     * Set a write_all scope or scopes that are allowed to write to the resource (all articles)
     *
     * @param array $writeAllScope
     *
     * @return $this
     */
    public function setWriteAllScope(array $writeAllScope)
    {
        if(!empty($writeAllScope)) {
            $this->writeAllScope = $this->parseScopeArray($writeAllScope);
        }

        return $this;
    }

    /**
     * Return only the scope that is targeted by a specific http verb
     *
     * @param string $httpMethod
     *
     * @return ScopeSet
     */
    public function getTargetScopeSetForHttpMethod($httpMethod)
    {
        $target = null;
        $httpMethod = strtoupper($httpMethod);

        switch ($httpMethod) {
            case 'GET' :
            case 'OPTIONS' :
            case 'HEAD' :
                $target = $this->readScope;
                break;
            case 'POST' :
            case 'PUT' :
            case 'PATCH' :
            case 'DELETE' :
                $target = $this->writeScope;
                break;
        }

        if ($target === null || $target->count() == 0) {
            throw new DeniedScopeException("HTTP $httpMethod to " . $this->routeName . " has no targeted scope(s). Update your configuration.");
        }

        return $target;
    }

    /**
     * Convert any strings to scope objects and place in a set
     *
     * @param array $scopes
     *
     * @return ScopeSet
     */
    protected function parseScopeArray(array &$scopes)
    {
        foreach ($scopes as $key => $value) {
            if (is_string($value)) {
                $scopes[$key] = new Scope($value);
            }
        }

        return new ScopeSet($scopes);
    }
}