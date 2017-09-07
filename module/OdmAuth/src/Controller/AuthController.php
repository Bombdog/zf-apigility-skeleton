<?php

namespace OdmAuth\Controller;

use OAuth2\Response as OAuth2Response;
use OdmAuth\Service\OdmAuthService;
use OdmAuth\Request\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * Validate "oauth" requests.
 * Only does a check on a password request and issues a grant.
 * No other request types are supported.
 *
 * Class AuthController
 * @package OdmAuth\Controller
 */
class AuthController extends AbstractActionController
{
    /**
     * @var OdmAuthService
     */
    protected $service;

    /**
     * AuthController constructor.
     *
     * @param OdmAuthService $service
     */
    public function __construct(OdmAuthService $service)
    {
        $this->service = $service;
    }

    /**
     * Token Action (/oauth)
     */
    public function tokenAction()
    {
        $request = $this->getRequest();
        if (! $request instanceof HttpRequest) {
            // not an HTTP request; nothing left to do
            return;
        }

        if ($request->isOptions()) {
            // OPTIONS request.
            // This is most likely a CORS attempt; as such, pass the response on.
            return $this->getResponse();
        }

        return $this->service->handleTokenRequest($request);
    }


    /**
     * @param OAuth2Response $response
     * @return ApiProblemResponse|\Zend\Stdlib\ResponseInterface
     *
    protected function getErrorResponse(OAuth2Response $response)
    {
        if ($this->isApiProblemErrorResponse()) {
            return $this->getApiProblemResponse($response);
        }

        return $this->setHttpResponse($response);
    }*/

    /**
     * Map OAuth2Response to ApiProblemResponse
     *
     * @param OAuth2Response $response
     * @return ApiProblemResponse
     *
    protected function getApiProblemResponse(OAuth2Response $response)
    {
        $parameters       = $response->getParameters();
        $errorUri         = isset($parameters['error_uri'])         ? $parameters['error_uri']         : null;
        $error            = isset($parameters['error'])             ? $parameters['error']             : null;
        $errorDescription = isset($parameters['error_description']) ? $parameters['error_description'] : null;

        return new ApiProblemResponse(
            new ApiProblem(
                $response->getStatusCode(),
                $errorDescription,
                $errorUri,
                $error
            )
        );
    }
    */
}
