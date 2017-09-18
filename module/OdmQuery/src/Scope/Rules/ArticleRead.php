<?php

namespace Bot\Core\Api\OAuth\Scope\Rule;

use OdmQuery\Scope\ScopeRuleAbstract;

/**
 * article:read
 * Anon users may see articles without registering.
 *
 * @package Bot\Core\Api\OAuth\Scope\Rule
 */
class ArticleRead extends ScopeRuleAbstract
{
    /**
     * The bids:read scope is for anon users.
     * The priority is high.
     */
    public function getPriority()
    {
        return 110;
    }

    /**
     * IMPORTANT: anon users should NEVER see articles that are invisible
     * @return array
     */
    public function getFilter()
    {
        return [
            [
                'field' => 'visible',
                'type'  => 'eq',
                'values' => true,
                'where' => 'and'
            ]
        ];
    }

    /**
     * Default fields for anon users
     * @return array
     */
    public function getDefaultFields()
    {
        return [
            'title','description','sequence'
        ];
    }

    /**
     * IMPORTANT: anon users should NEVER see maximum or proxy
     * @return array
     */
    public function getBlackListedFields()
    {
        return ['status'];
    }
}
