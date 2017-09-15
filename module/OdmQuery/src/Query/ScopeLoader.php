<?php

namespace Bot\Core\Api\OAuth\Scope;

use Bot\Core\Api\OAuth\Identity\IdentityContextInterface;

/**
 * Scope loading: who are you (identity)? and what are you trying to access (scopes)?
 *
 * This is a loader to assemble customised filters that restrict access to data based on the scopes being accessed
 * and the identity of the accessor. For example your resource fetchall() might return a collection based on the scopes
 * available to the identity (user) and the scopes that apply to the resource (read,write,write_all etc). There is no
 * general rule: a user might have the invoices:read privilage, that means they can read their own invoices but nobody
 * elses. Conversely, the lots:read privilage allows a user to read ALL lots. There is no general rule for all scopes,
 * so specific scopes can be defined with or without filters and the filters, if they are defined, are totally flexible.
 * In the case of lots:read we are permissive and might not need any filtering, but invoices:read is very sensitive
 * and we have to make sure that one user (identity) cannot access another user's data.
 *
 * Additionally, we have the concept of read-only fields. Read-only fields can be passed into a hydrator to mask off
 * fields and make them unavailable for editing. Credit cards are the best example, we can edit card status, but never
 * the expiry date or other important details that identify the card with the payment gateway.
 *
 * Class FilterFactory
 * @package Bot\Core\Api\OAuth\Scope
 */
class ScopeLoader
{
    /**
     *
     * @var IdentityContextInterface
     */
    private $identity;

    /**
     * Filter namespace where all filters reside
     * @var string
     */
    private $rulesNamespace;

    /**
     * Array of any previously loaded scope rulesets
     * @var array
     */
    private $loadedRules;

    /**
     * Create a loader by passing an identity.
     * @param IdentityContextInterface $identity
     */
    public function __construct(IdentityContextInterface $identity)
    {
        $this->identity = $identity;
        $this->rulesNamespace = __NAMESPACE__ . '\Rule';
    }

    /**
     * Merge a request filter with the scopes (scope filters). Scope filters are found in scope rules and should
     * always override anything that is requested by the user. For example if I am a user looking at invoices I
     * should only see my own invoices and never be able to see anyone else's. Therefore the userId is locked
     * in the invoices:read scope.
     *
     * @param array $filter
     * @param array $resourceScopes
     * @return array
     */
    public function mergeRequestFilterWithScopes(array $filter, array $resourceScopes)
    {
        $scopeRules = $this->getRulesForScopes($resourceScopes);
        if(!empty($scopeRules)) {

            /** @var ScopeRuleInterface $scopeRule */
            foreach($scopeRules as $scopeRule) {
                $scopeFilter = $scopeRule->getFilter($this->identity);
                if($scopeFilter !== null) {
                    foreach ($scopeFilter as $override) {
                        $overrideApplied = false;
                        foreach ($filter as $key => $searchTerm) {
                            if($searchTerm['field'] == $override['field']) {
                                $filter[$key] = $override;
                                $overrideApplied = true;
                            }
                        }
                        if(!$overrideApplied) {
                            $filter[] = $override;
                        }
                    }
                }
            }
        }

        return $filter;
    }

    /**
     * Get the read-only fields (if applicable).
     * The filter with the highest priority (last element) decides the read-only fields.
     * @param array $resourceScopes
     * @return array|null
     */
    public function getReadOnlyFields(array $resourceScopes)
    {
        $rules = $this->getRulesForScopes($resourceScopes);
        if(!empty($rules)) {
            /** @var ScopeRuleInterface $rule */
            $rule = end($rules);
            return $rule->getReadonlyFields();
        }

        return null;
    }

    /**
     * The blacklisted fields in the resource scope, or null if none
     * The filter with the highest priority (last element) decides the read-only fields.
     * @param array $resourceScopes
     * @return array
     */
    public function getBlackListedFields(array $resourceScopes)
    {
        $rules = $this->getRulesForScopes($resourceScopes);
        if(!empty($rules)) {
            /** @var ScopeRuleInterface $rule */
            $rule = end($rules);
            return $rule->getBlackListedFields();
        }

        return null;
    }

    /**
     * The default fields in the resource scope, or null if none.
     * The filter with the highest priority (last element) decides the read-only fields.
     * @param array $resourceScopes
     * @return array
     */
    public function getDefaultFields(array $resourceScopes)
    {
        $rules = $this->getRulesForScopes($resourceScopes);
        if(!empty($rules)) {
            /** @var ScopeRuleInterface $rule */
            $rule = end($rules);
            return $rule->getDefaultFields();
        }

        return null;
    }

    /**
     * Load and return rules for an a given scope or scopes. This is done lazy-load style and cached.
     * The $resourceScopes argument is from the resource, usually one of allowedReadScopes, allowedWriteScopes
     *   or the writeAllScope. (A user may be assigned many scopes but these are the ones they are using)
     * @param array $resourceScopes
     * @return array
     */
    public function getRulesForScopes(array $resourceScopes)
    {
        $key = md5(implode('',$resourceScopes));

        if( !isset($this->loadedRules[$key]) ) {
            $count = 0;
            $filters = [];
            $scopesOfInterest = $this->identity->getAuthorisedScopes($resourceScopes);
            foreach($scopesOfInterest as $scopeToLoad) {
                $filter = $this->getRuleForScope($scopeToLoad);
                if( $filter !== null ) {
                    $index = $filter->getPriority() + $count;
                    $filters[$index] = $filter;
                    $count ++;
                }
            }
            $this->loadedRules[$key] = $filters;
        }

        return $this->loadedRules[$key];
    }

    /**
     * Get a filter for a named scope, for example invoices:read would return a filter of type "InvoiceRead"
     * If no filter exists for the scope it will simply return null.
     * @param $scope
     * @return ScopeRuleInterface
     */
    public function getRuleForScope($scope)
    {
        $rule = null;
        $parts = explode(':',$scope);
        $className = $this->rulesNamespace . "\\" . ucfirst(rtrim($parts[0], 's'));
        $className .= str_replace('_','',ucwords($parts[1],'_'));
        if(class_exists($className)) {
            $rule = new $className();
        }

        return $rule;
    }
}
