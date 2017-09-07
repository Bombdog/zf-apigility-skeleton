<?php

namespace Entity\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\LockMode;
use Doctrine\ODM\MongoDB\Mapping\MappingException;

/**
 * Class extended by the other repositories to share common methods
 * @package Entity\Repository
 */
abstract class BaseRepository extends DocumentRepository
{
    /**
     * Find entity directly by its id.
     * Adapted for our use case where we might pass an integer sequence id instead of a mongoId
     *
     * @param string|object $id The identifier
     * @param int $lockMode
     * @param int $lockVersion
     *
     * @throws MappingException
     * @throws LockException
     * @return object The document.
     */
    public function find($id, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        if (is_int($id) || (is_string($id) && ctype_digit($id))) {
            return $this->findOneBy(['sequence' => (int) $id]);
        }

        return parent::find($id, $lockMode, $lockVersion);
    }
}