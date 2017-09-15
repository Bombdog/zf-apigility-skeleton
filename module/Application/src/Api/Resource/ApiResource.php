<?php

namespace Application\Api\Resource;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use DoctrineMongoODMModule\Paginator\Adapter\DoctrinePaginator;
use Entity\Hydrator\BaseHydratorExtractor;
use Entity\Repository\BaseRepository;
use Zend\Paginator\Paginator;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

/**
 * Base class for an example Restful API.
 * This can be used as a base class but it's likely that you will have custom requirements for
 * your Restful APIS. Consider extending DoctrineResource and copy what you need from here.
 * We not be talking no HAL by the way, sorry folks.
 *
 * @package Events\V1\Rest\Event
 */
class ApiResource extends AbstractResourceListener
{
    /**
     * Doctrine ODM document manager
     * @var DocumentManager
     */
    protected $dm;

    /**
     * Hydrator, based on Phpro\DoctrineHydrationModule
     * @var BaseHydratorExtractor
     */
    protected $hydrator;

    /**
     * ApiResource constructor. Requires the doc manager and a hydrator.
     *
     * @param DocumentManager $dm
     * @param BaseHydratorExtractor $hydrator
     */
    public function __construct(DocumentManager $dm, BaseHydratorExtractor $hydrator)
    {
        $this->dm = $dm;
        $this->hydrator = $hydrator;
    }

    /**
     * Get odm document manager
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->dm;
    }

    /**
     * Get the hydrator
     * @return BaseHydratorExtractor
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * Fetch a record by id.
     * Id can be either an integer sequence or a mongoId.
     * This is permitted for users who have scopes matching readScope in the route config.
     * You must set allowed scopes in the options section of the route.
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        /** @var DocumentManager $dm */
        $dm = $this->getDocumentManager();

        /** @var BaseRepository $repo */
        $repo = $dm->getRepository($this->getEntityClass());
        $entity = $repo->find($id);

        if ($entity !== null) {
            /** @var BaseHydratorExtractor $hydrator */
            $hydrator = $this->getHydrator();
            $result = $hydrator->extract($entity);
            if (empty($result)) {
                return new ApiProblem(400, "None of the requested fields were present in the result");
            }
            return $result;
        }

        # returning null causes apigility to raise a 404 not found error
        return null;
    }

    /**
     * Fetch all records from a collection or a subset based on a filter.
     * This is permitted for users who have scopes matching readScope in the route config.
     * @param array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        /** @var DocumentManager $dm */
        $dm = $this->getDocumentManager();

        /** @var DocumentRepository $repo */
        $repo = $dm->getRepository($this->getEntityClass());
        $qb = $repo->createQueryBuilder();

        /*
        $view = $context->getPrimaryView();
        if (!empty($view)) {
            $qb->select($view);
        }*/

        $metadata = $dm->getMetadataFactory()->getAllMetadata();

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

        # Get cursor and apply pagination (unfiltered cursor from Mongo find)
        /** @var \Doctrine\ODM\MongoDB\Cursor $cursor */
        $cursor = $qb->getQuery()->execute();

        # Collection class (is actually subclass of Zend Paginator class)
        $collectionClass = $this->getCollectionClass();

        /** @var Paginator $collection */
        $collection = new $collectionClass(new DoctrinePaginator($cursor));

        /*
        $collection->setDefaultItemCountPerPage($context->getPageSize());
        $collection->setCurrentPageNumber($context->getPage());
        $collection->setItemCountPerPage($context->getPageSize());
        */

        /*
        if ($context->getPage() > $collection->getCurrentPageNumber()) {
            return new ApiProblem(409, 'Invalid page provided');
        }
        */

        /** @var BaseHydratorExtractor $hydrator */
        $hydrator = $this->getHydrator();
        return $hydrator->extractCollection($collection, true);
    }

    /**
     * Create a resource item
     * This is permitted for users who have scopes matching allowedWriteScopes.
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }

        if (isset($data['id'])) {
            return new ApiProblem(405, "Method Not Allowed (attempt to modify, use PATCH instead)");
        }

        /*
        /** @var ContextBuilder $context *
        $context = $sl->get('api-context');
        */

        /** @var BaseHydratorExtractor $hydrator */
        $hydrator = $this->getHydrator();
        $hydrator->resetView();
        $entityClass = $this->getEntityClass();


        $entity = $hydrator->hydrateWithContext($data, new $entityClass, 0);

        // $entity = $hydrator->hydrateWithContext($data, new $entityClass, $context->getUserId());


        if ($entity instanceof ApiProblem) {
            return $entity;
        }

        /** @var DocumentManager $dm */
        $dm = $this->getObjectManager();
        $dm->persist($entity);
        $dm->flush($entity);

        return $hydrator->extract($entity);
    }

    /**
     * Update ( PATCH ) an entity.
     * This is permitted for users who have scopes matching allowedWriteScopes.
     * Additionally, if the entity is owned and the identity is not the owner (or doesn't have :write_all)
     * then the request will be refused.
     * Note that a PATCH has no effect on fields that have been marked as read-only.
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }
        unset($data['id']);

        /** @var DocumentManager $dm */
        $dm = $this->getObjectManager();

        /** @var BaseRepository $repo */
        $repo = $dm->getRepository($this->getEntityClass());
        $entity = $repo->find($id);
        if ($entity === null) {
            return new ApiProblem(404, "Not Found");
        }

        /*
        $sl = $this->getServiceManager();

        /** @var ContextBuilder $context *
        $context = $sl->get('api-context');

        if (!$context->isWriteAllAuthorised() && !$context->isOwnedEntity($entity)) {
            return new ApiProblem(403, "Forbidden, missing ownership rights.");
        }*/

        /** @var BaseHydratorExtractor $hydrator */
        $hydrator = $this->getHydrator();
        $hydrator->resetView();


        $entity = $hydrator->hydrateWithContext($data, $entity, 0);
        // $entity = $hydrator->hydrateWithContext($data, $entity, $context->getUserId());


        if ($entity instanceof ApiProblem) {
            return $entity;
        }

        $dm->flush($entity);
        return $hydrator->extract($entity);
    }

    /**
     * Delete an entity.
     * This is permitted for users who have scopes matching allowedWriteScopes.
     * Additionally, if the entity is owned and the identity is not the owner (or doesn't have :write_all)
     * then the request will be refused.
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        /** @var ContextBuilder $context *
            $context = $sl->get('api-context');
         */

        /** @var DocumentManager $dm */
        $dm = $this->getObjectManager();

        /** @var BaseRepository $repo */
        $repo = $dm->getRepository($this->getEntityClass());
        $entity = $repo->find($id);

        if ($entity === null) {
            return new ApiProblem(404, "Not Found " . $id);
        }

        /*
        if (!$context->isWriteAllAuthorised() && !$context->isOwnedEntity($entity)) {
            return new ApiProblem(403, "Forbidden, missing ownership rights.");
        }*/

        $dm->remove($entity);
        $dm->flush($entity);

        return true;
    }

}
