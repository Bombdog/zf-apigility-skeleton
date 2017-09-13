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
     * @var array
     */
    protected $readScope = [];

    /**
     * @var array
     */
    protected $writeScope = [];

    /**
     * @var array
     */
    protected $writeAllScope = [];

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
            $this->readScope = [$read, $write, $writeAll];
            $this->writeScope = [$write, $writeAll];
            $this->writeAllScope = [$writeAll];
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
            $this->readScope = $this->normalizeScopeArray($readScope);
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
            $this->writeScope = $this->normalizeScopeArray($writeScope);
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
            $this->writeAllScope = $this->normalizeScopeArray($writeAllScope);
        }

        return $this;
    }

    /**
     * Return only the scope that is targeted by a specific http verb
     *
     * @param string $httpMethod
     *
     * @return array
     */
    public function getTargetScopeForHttpMethod($httpMethod)
    {
        $targets = null;
        $httpMethod = strtoupper($httpMethod);

        switch ($httpMethod) {
            case 'GET' :
            case 'OPTIONS' :
            case 'HEAD' :
                $targets = $this->readScope;
                break;
            case 'POST' :
            case 'PUT' :
            case 'PATCH' :
            case 'DELETE' :
                $targets = array_merge($this->writeScope, $this->writeAllScope);
                break;
        }

        if ($targets === null || count($targets) == 0) {
            throw new DeniedScopeException("HTTP $httpMethod to " . $this->routeName . " has no targeted scope(s). Update your configuration.");
        }

        return $targets;
    }

    /**
     * Convert any strings to scope objects
     *
     * @param array $scopes
     */
    protected function normalizeScopeArray(array &$scopes)
    {
        foreach ($scopes as $key => $value) {
            if (is_string($value)) {
                $scopes[$key] = new Scope($value);
            }
        }
    }
}