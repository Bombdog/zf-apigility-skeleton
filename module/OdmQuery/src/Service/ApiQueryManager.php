<?php

namespace OdmQuery\Service;

use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use DoctrineMongoODMModule\Paginator\Adapter\DoctrinePaginator;
use OdmAuth\Request\PagedQueryInterface;
use OdmQuery\Query\Select\Selection;
use OdmQuery\Scope\ScopeRuleInterface;
use Zend\Paginator\Paginator;
use ZF\ApiProblem\ApiProblem;
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
     * @var ScopeRuleInterface
     */
    protected $targetScopeRules;

    /**
     * ApiQueryManager constructor.
     *
     * @param PagedQueryInterface $requestQuery
     * @param $targetScopeRules
     */
    public function __construct(PagedQueryInterface $requestQuery, $targetScopeRules)
    {
        $this->requestQuery = $requestQuery;

        if($targetScopeRules instanceof ScopeRuleInterface) {
            $this->targetScopeRules = $targetScopeRules;
        }

        $this->selection = new Selection($requestQuery->getFields());

        if($this->targetScopeRules !== null) {
            $this->selection->setDefaults($this->targetScopeRules->getDefaultFields());
            $this->selection->setBlackList($this->targetScopeRules->getBlackListedFields());
        }
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
        $readOnly = [];
        if($this->targetScopeRules !== null) {
            $readOnly = $this->targetScopeRules->getReadonlyFields();
        }

        return $readOnly;
    }

    /**
     * Get the primary and secondary as an array to pass into the hydrator.
     * @return array
     */
    public function getView()
    {
        return [
            'primary' => $this->selection->getPrimary(),
            'secondary' => $this->selection->getSecondary()
        ];
    }

    /**
     * Build and return a query.
     * This is used for auto-populating your query builder using pre-screened search terms
     */
    public function buildQuery(Builder $builder, $options = [])
    {
        # secured select fields
        $view = $this->selection->getPrimary();
        if (!empty($view)) {
            $builder->select($view);
        }

        # secured filter
        $filter = $this->getMergedFilter();
        if(!empty($filter) && $this->filterManager !== null) {
            $this->filterManager->filter($builder, $this->classMetadata, $filter);
        }

        # secured sort
        //$sort = $this->getMergedFilter();
        $sort = [];
        if(!empty($sort) && $this->orderByManager !== null) {
            $this->orderByManager->orderBy($builder, $this->classMetadata, $sort);
        }

        return $builder->getQuery($options);
    }

    /**
     * Populate a paginator using requested parameters
     *
     * @param string $collectionClass
     * @param $cursor
     *
     * @return Paginator|ApiProblem
     */
    public function buildPaginator($collectionClass, Cursor $cursor)
    {
        /** @var Paginator $paginator */
        $paginator = new $collectionClass(new DoctrinePaginator($cursor));
        $paginator->setDefaultItemCountPerPage($this->requestQuery->getPageSize());
        $paginator->setCurrentPageNumber($this->requestQuery->getPage());
        $paginator->setItemCountPerPage($this->requestQuery->getPageSize());

        if ($this->requestQuery->getPage() > $paginator->getCurrentPageNumber()) {
            return new ApiProblem(409, 'Invalid page provided');
        }

        return $paginator;
    }

    /**
     * Merge the requested filter with any restrictions incurred by the scope.
     *
     * @return array|mixed
     */
    private function getMergedFilter()
    {
        $filter = $this->requestQuery->getFilter();

        if($this->targetScopeRules !== null) {
            $scopeFilter = $this->targetScopeRules->getFilter();

            if (!empty($scopeFilter)) {
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
        }

        return $filter;
    }
}