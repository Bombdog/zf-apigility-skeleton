<?php

namespace OdmAuth\Controller;

use OdmAuth\Service\OdmAuthService;
use OdmAuth\Request\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Validate "oauth" requests.
 * Only does a check on a password request and issues a grant.
 * No other request types are supported at this time.
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
}
