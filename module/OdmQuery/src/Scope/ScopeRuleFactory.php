<?php

namespace OdmQuery\Scope;

use OdmScope\Scope\Scope;

/**
 * Build a scope rule (set of restrictions) from an array of affected (target) scopes.
 * Generally there should be only one affected scope, but if there are two or more the MOST RESTRICTIVE
 * set of rules should be applied.
 *
 * @package OdmQuery\Query
 */
class ScopeRuleFactory
{
    private function __construct() {}

    /**
     * Get a rule for a scope or scopes.
     *
     * @param array $scopes
     *
     * @return ScopeRuleInterface
     */
    public static function getInstance(array $scopes)
    {
        if(empty($scopes)) {
            # it's ok if there are no rules, but there MUST be at least one scope
            throw new \InvalidArgumentException("Cannot create a scope rules without scopes");
        }

        $lastPriority = -1;
        $priorityRule = null;

        foreach ($scopes as $key => $scope) {
            if(!$scope instanceof Scope) {
                throw new \InvalidArgumentException("Scope match $key is not a scope.");
            }

            # create class name
            # e.g. "articles:read" becomes "ArticleRead"
            $parts = explode(':',(string) $scope);
            $className = __NAMESPACE__ . '\\Rules\\' . ucfirst(rtrim($parts[0], 's'));
            $className .= str_replace('_','',ucwords($parts[1],'_'));
            if (class_exists($className)) {
                /** @var ScopeRuleInterface $rule */
                $rule = new $className();
                if($rule->getPriority() > $lastPriority) {
                    $priorityRule = $rule;
                }
            }
        }

        return $priorityRule;
    }
}
