<?php
namespace Entity;

use ZF\ApiProblem\Exception\ProblemExceptionInterface;

/**
 * When an entity has failed some basic validation and cannot be persisted or updated.
 * Class EntityValidationException
 * @package Entity
 */
class EntityValidationException extends \LogicException implements ProblemExceptionInterface
{
    use ProblemExceptionTrait;

    /**
     * This is a 400 error by default.
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message = "", $code = 400, \Exception $previous = null)
    {
        parent::__construct($message,$code,$previous);
    }
}
