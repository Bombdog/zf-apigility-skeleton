<?php

namespace OdmQuery\Service;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use OdmAuth\Request\PagedQueryInterface;
use OdmQuery\Query\Select\Selection;
use OdmQuery\Scope\ScopeRuleAbstract;
use OdmQuery\Scope\ScopeRuleFactory;
use ZF\Doctrine\QueryBuilder\Filter\Service\ODMFilterManager;
use ZF\Doctrine\QueryBuilder\OrderBy\Service\ODMOrderByManager;

/**
 * Query builder manager.
 * Uses the zf-doctrine-querybuilder filtering and sorting managers.
 * This service is responsible for merging requests with scoped contexts to provide secured ODM queries.
 */
class ApiQueryManager
{
    /**
     * The query being managed
     * @var PagedQueryInterface
     */
    protected $requestQuery;

    /**
     * The filter manager
     * @var ODMFilterManager
     */
    protected $filterManager;

    /**
     * The order manager
     * @var ODMOrderByManager
     */
    protected $orderByManager;

    /**
     * Doctrine class metadata
     * @var ClassMetadata
     */
    protected $classMetadata;

    /**
     * Fields select part of query
     * @var Selection
     */
    protected $selection;

    /**
     * Scope rule for target
     * @var ScopeRuleAbstract
     */
    protected $targetScope;

    /**
     * ApiQueryManager constructor.
     *
     * @param PagedQueryInterface $requestQuery
     */
    public function __construct(PagedQueryInterface $requestQuery, array $matchingScopes)
    {
        $this->requestQuery = $requestQuery;
        $this->targetScope = ScopeRuleFactory::getInstance($matchingScopes);
        $this->selection = new Selection($requestQuery->getFields());
        $this->selection->setDefaults($this->targetScope->getDefaultFields());
        $this->selection->setBlackList();
    }

    /**
     * @param ODMFilterManager $filterManager
     */
    public function setFilterManager(ODMFilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * @param ODMOrderByManager $orderByManager
     */
    public function setOrderByManager(ODMOrderByManager $orderByManager)
    {
        $this->orderByManager = $orderByManager;
    }

    /**
     * @param ClassMetadata $metadata
     */
    public function setClassMetadata(ClassMetadata $metadata)
    {
        $this->classMetadata = $metadata;
    }

    /**
     * Get the read only fields of the target scope (if any)
     */
    public function getReadonlyFields()
    {
        return $this->targetScope->getReadonlyFields();
    }

    /**
     * Build and return a query
     */
    public function buildQuery(Builder $builder, $options = [])
    {
        # select fields
        $view = $this->selection->getPrimary();
        if (!empty($view)) {
            $builder->select($view);
        }


        /*
        if ($context->hasFilter()) {
            /** @var ODMFilterManager $filterManager *
            $filterManager = $sl->get('ZfDoctrineQueryBuilderFilterManagerOdm');
            $filterManager->filter($qb, $metadata[0], $context->getFilter());
        }

        if ($context->hasSort()) {
            /** @var ODMOrderByManager $orderByManager *
            $orderByManager = $sl->get('ZfDoctrineQueryBuilderOrderByManagerOdm');
            $orderByManager->orderBy($qb, $metadata[0], $context->getSort());
        }*/

        return $builder->getQuery($options);
    }

    /**
     * Merge the requested filter with any restrictions incurred by the scope.
     *
     * @return array|mixed
     */
    private function getMergedFilter()
    {
        $filter = $this->requestQuery->getFilter();
        $scopeFilter = $this->targetScope->getFilter();

        if ($scopeFilter !== null) {
            foreach ($scopeFilter as $override) {
                $overrideApplied = false;
                foreach ($filter as $key => $searchTerm) {
                    if ($searchTerm['field'] == $override['field']) {
                        $filter[$key] = $override;
                        $overrideApplied = true;
                    }
                }
                if (!$overrideApplied) {
                    $filter[] = $override;
                }
            }
        }

        return $filter;
    }
}