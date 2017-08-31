<?php
namespace Entity;

use ZF\ApiProblem\Exception\ProblemExceptionInterface;

/**
 * When an an invalid value is passed into an entity.
 * Made compatible with Apigility via ProblemExceptionInterface.
 * Class EntityInvalidArgumentException
 * @package Entity
 */
class EntityInvalidArgumentException extends \InvalidArgumentException implements ProblemExceptionInterface
{
    use ProblemExceptionTrait;

    /**
     * 400 Error by default - client has made a bad request.
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message = "", $code = 400, \Exception $previous = null)
    {
        parent::__construct($message,$code,$previous);
    }
}
