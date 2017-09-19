<?php

namespace Application\Api\Resource;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Entity\Hydrator\BaseHydratorExtractor;
use Entity\Repository\BaseRepository;
use OdmQuery\Service\ApiQueryManager;
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
     * API query manager
     * @var ApiQueryManager
     */
    protected $qm;

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
     *
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->dm;
    }

    /**
     * Get the hydrator
     *
     * @return BaseHydratorExtractor
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * Get the query manager
     *
     * @return ApiQueryManager
     */
    public function getQueryManager()
    {
        return $this->qm;
    }

    /**
     * Set the query manager, remembering to apply any restrictions to the hydrator / extractor
     *
     * @param ApiQueryManager $qm
     */
    public function setQueryManager(ApiQueryManager $qm)
    {
        $this->qm = $qm;
        $this->hydrator->setViewableFields($qm->getView());
        $this->hydrator->setReadOnlyFields($qm->getReadonlyFields());
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
        $query = $this->qm->buildQuery($qb);

        # Get cursor and apply pagination (unfiltered cursor from Mongo find)
        /** @var \Doctrine\ODM\MongoDB\Cursor $cursor */
        $cursor = $query->execute();

        # Collection class injected by apigility (is usually empty subclass of Zend\Paginator\Paginator)
        $paginator = $this->qm->buildPaginator($this->getCollectionClass(), $cursor);

        if($paginator instanceof ApiProblem) {
            return $paginator;
        }

        return $this->hydrator->extractCollection($paginator, true);
    }

    /**
     * Create a resource item
     * This is permitted for users who have scopes matching allowedWriteScopes.
     *
     * @todo: apply user id to hydration context
     *
     * @param  mixed $data
     *
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

        /** @var BaseHydratorExtractor $hydrator */
        $hydrator = $this->getHydrator();
        $hydrator->resetView();
        $entityClass = $this->getEntityClass();
        $entity = $hydrator->hydrateWithContext($data, new $entityClass, 0);

        if ($entity instanceof ApiProblem) {
            return $entity;
        }

        /** @var DocumentManager $dm */
        $this->dm->persist($entity);
        $this->dm->flush($entity);

        return $hydrator->extract($entity);
    }

    /**
     * Update ( PATCH ) an entity.
     * This is permitted for users who have scopes matching allowedWriteScopes.
     * Additionally, if the entity is owned and the identity is not the owner (or doesn't have :write_all)
     * then the request will be refused.
     * Note that a PATCH has no effect on fields that have been marked as read-only.
     *
     * @todo: apply user id to hydration context
     * @param  mixed $id
     * @param  mixed $data
     *
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }
        unset($data['id']);

        /** @var BaseRepository $repo */
        $repo = $this->dm->getRepository($this->getEntityClass());
        $entity = $repo->find($id);
        if ($entity === null) {
            return new ApiProblem(404, "Not Found");
        }

        /*
        @todo: check ownership of edited record
        if (!$context->isWriteAllAuthorised() && !$context->isOwnedEntity($entity)) {
            // !$user->isWriteAllAuthorised() && !$user->owns($entity)
            return new ApiProblem(403, "Forbidden, missing ownership rights.");
        }*/

        /** @var BaseHydratorExtractor $hydrator */
        $hydrator = $this->getHydrator();
        $hydrator->resetView();
        $entity = $hydrator->hydrateWithContext($data, $entity, 0);

        if ($entity instanceof ApiProblem) {
            return $entity;
        }

        $this->dm->flush($entity);

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
        /** @var BaseRepository $repo */
        $repo = $this->dm->getRepository($this->getEntityClass());
        $entity = $repo->find($id);

        if ($entity === null) {
            return new ApiProblem(404, "Not Found " . $id);
        }

        /*
        @todo: check ownership of edited record
        if (!$context->isWriteAllAuthorised() && !$context->isOwnedEntity($entity)) {
            // !$user->isWriteAllAuthorised() && !$user->owns($entity)
            return new ApiProblem(403, "Forbidden, missing ownership rights.");
        }*/

        $this->dm->remove($entity);
        $this->dm->flush($entity);

        return true;
    }
}
