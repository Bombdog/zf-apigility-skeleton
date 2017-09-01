<?php

namespace Application\Api\Response;

use Zend\Http\Response;

/**
 * Represents an successful transaction on the API.
 * Used mainly for calls that don't return any data, e.g. 2xx response to an RPC call.
 */
class ApiSuccessResponse extends Response
{
    /**
     * Flags to use with json_encode
     *
     * @var int
     */
    protected $jsonFlags = 0;

    /**
     * URL describing the problem type; defaults to HTTP status codes
     * @var string
     */
    protected $type = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html';

    /**
     * Additional detail of response
     * @var string
     */
    protected $detail;

    /**
     * Create a simple json response packet with custom http status and detail.
     *
     * @param int $httpStatus
     * @param string $detail
     */
    public function __construct($httpStatus, $detail = null)
    {
        $this->setCustomStatusCode($httpStatus);
        $this->detail = $detail;

        if (defined('JSON_UNESCAPED_SLASHES')) {
            $this->jsonFlags = constant('JSON_UNESCAPED_SLASHES');
        }
    }

    /**
     * Retrieve the API-Sucess detail (custom message)
     * @return string
     */
    protected function getDetail()
    {
        return $this->detail;
    }

    /**
     * Retrieve the content
     *
     * Serializes the composed ApiProblem instance to JSON.
     *
     * @return string
     */
    public function getContent()
    {
        $content = [
            "type" => $this->type,
            "title" => $this->getReasonPhrase(),
            "status" => $this->getStatusCode()
        ];

        if ($this->getDetail() !== null) {
            $content['detail'] = $this->getDetail();
        }

        return json_encode($content, $this->jsonFlags);
    }

    /**
     * Retrieve headers
     *
     * Proxies to parent class, but then checks if we have an content-type
     * header; if not, sets it, with a value of "application/json".
     *
     * @return \Zend\Http\Headers
     */
    public function getHeaders()
    {
        $headers = parent::getHeaders();
        if (!$headers->has('content-type')) {
            $headers->addHeaderLine('content-type', 'application/json');
        }
        
        return $headers;
    }

    /**
     * Override reason phrase handling
     *
     * If no corresponding reason phrase is available for the current status
     * code, return "Unknown Error".
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        if (!empty($this->reasonPhrase)) {
            return $this->reasonPhrase;
        }

        if ($this->statusCode < 300 && isset($this->recommendedReasonPhrases[$this->statusCode])) {
            return $this->recommendedReasonPhrases[$this->statusCode];
        }

        return 'Unknown Error';
    }
}
