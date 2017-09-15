<?php

namespace OdmQuery\Service;

use Doctrine\ODM\MongoDB\Query\Builder;
use OdmAuth\Request\PagedQueryInterface;
use OdmQuery\Query\ApiQuery;
use OdmQuery\Query\Select\Selection;
use ZF\Doctrine\QueryBuilder\Filter\Service\ODMFilterManager;
use ZF\Doctrine\QueryBuilder\OrderBy\Service\ODMOrderByManager;

/**
 * Query builder manager.
 * Uses the zf-doctrine-querybuilder filter and sorting managers.
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
     * @var Selection
     */
    protected $selection;

    /**
     * ApiQueryManager constructor.
     *
     * @param PagedQueryInterface $requestQuery
     */
    public function __construct(PagedQueryInterface $requestQuery)
    {
        $this->requestQuery = $requestQuery;
        $this->selection = new Selection($requestQuery->getFields());
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


    public function setScopeRestriction()
    {

    }



    /**
     * Build and return a query
     */
    public function buildQuery(Builder $builder, $options=[])
    {
        # select fields
        $view = $this->selection->getPrimary();
        if (!empty($view)) {
            $builder->select($view);
        }

        // $metadata = $dm->getMetadataFactory()->getAllMetadata();

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
}