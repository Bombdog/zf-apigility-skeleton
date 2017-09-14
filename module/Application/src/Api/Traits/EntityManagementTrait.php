<?php

namespace Application\Api\Traits;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Created by PhpStorm.
 * User: andyh
 * Date: 01/09/17
 * Time: 14:09
 */
trait EntityManagementTrait
{

    /**
     * @var DocumentRepository
     */
    protected $repository;

    /**
     * @return DocumentRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param DocumentRepository $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }


}