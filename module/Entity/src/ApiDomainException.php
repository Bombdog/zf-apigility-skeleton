<?php
namespace Entity;

use ZF\ApiProblem\Exception\ProblemExceptionInterface;

/**
 * General domain exception for HAPI repositories and services.
 * Made compatible with Apigility via ProblemExceptionInterface.
 * @package Entity
 */
class ApiDomainException extends \DomainException implements ProblemExceptionInterface
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
