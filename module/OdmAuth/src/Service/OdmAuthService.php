<?php

namespace OdmAuth\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Documents\UserRepository;
use Entity\Document\OAuth\AccessToken;
use Entity\Document\User;
use Entity\Util\Password;
use Zend\Http\Response as HttpResponse;
use OdmAuth\Request\Request as HttpRequest;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * Basics - only to allowing password grants via the users collection
 * @see bshaffer/oauth2-server-php
 */
class OdmAuthService
{
    /**
     * Doctrine connection
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * Time of expiry for any new tokens (default is half day)
     * @var int
     */
    protected $expiryTime = 43200;

    /**
     * OdmAuthService constructor.
     *
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Get a response object for composing a response
     *
     * @return HttpResponse
     */
    public function getResponse()
    {
        if (! $this->response) {
            $this->response = new HttpResponse();
            $headers = $this->response->getHeaders();
            $headers->addHeaderLine('Content-type', 'application/json');
        }

        return $this->response;
    }

    /**
     * Handle a request for an access token
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse
     */
    public function handleTokenRequest(HttpRequest $request)
    {
        if ($token = $this->grantAccessToken($request)) {
            // @see http://tools.ietf.org/html/rfc6749#section-5.1
            // server MUST disable caching in headers when tokens are involved
            $response = $this->getResponse();
            $response->setStatusCode(200);
            $headers = $response->getHeaders();
            $headers->addHeaderLine('Cache-Control', 'no-store');
            $headers->addHeaderLine('Pragma', 'no-cache');
        }

        return $this->getResponse();
    }

    /**
     * Try to create an access token.
     * Adapted from bshaffer/oauth2-server-php (OAuth2\Controller\TokenController)
     *
     * body params {
     *      grant_type=password (required)
     *      username=user@example.com (required)
     *      password=1234luggage (required)
     *      client_id=xxxxxxxxxx (optional)
     *      client_secret=xxxxxxxxxx (optional)
     *      scope=article:read (optional)
     * }
     *
     * @see https://www.oauth.com/oauth2-servers/access-tokens/password-grant/
     * @see https://www.oauth.com/oauth2-servers/access-tokens/access-token-response/
     *
     * @param HttpRequest $request
     *
     * @return null
     */
    private function grantAccessToken(HttpRequest $request)
    {
        # POST ONLY
        if (!$request->isPost()) {
            $this->setApiProblemResponse(405, 'invalid_request', 'The request method must be POST when requesting an access token', '#section-3.2');

            return null;
        }

        $auth = $request->getAuth();

        if($auth === null) {
            $this->setApiProblemResponse(400, 'invalid_request', 'Failed to parse the request');
        }

        /**
         * Determine grant type from request
         * and validate the request for that grant type
         */
        if (!$grantTypeIdentifier = $auth->getGrantType()) {
            $this->setApiProblemResponse(400, 'invalid_request', 'The grant type was not specified in the request');

            return null;
        }

        # Password only for now
        if ($grantTypeIdentifier != 'password') {
            $this->setApiProblemResponse(501, 'unsupported_grant_type', sprintf('Grant type "%s" not supported', $grantTypeIdentifier));

            return null;
        }

        # @todo: check client_id and client_secret

        # Search for user/password
        /** @var UserRepository $repo */
        $repo = $this->dm->getRepository('Entity\Document\User');

        /** @var User $user */
        $user = $repo->findOneBy([
            'username' => $auth->getUsername(),
            'enabled' => true
        ]);

        if($user !== null && Password::verify($auth->getPassword(),$user->getPassword())){

            // @todo: evaluate requested vs default scope

            $token = new AccessToken($this->expiryTime);
            $token->setUserId($user->getUsername())
                ->setClientId($auth->getClientId())
                ->setScope($user->getScope());

            // @todo incorporate a refresh token
            $this->dm->persist($token);
            $this->dm->flush($token);

            $response = $this->getResponse();
            $response->setContent(json_encode($token));
        }
        else {
            $this->setApiProblemResponse('401','invalid_grant','Invalid username and password combination');
        }
    }

    /**
     * Utility to generate API problem responses (same contract as OAuth2/ResponseInterface->setError)
     *
     * @param $statusCode
     * @param $name
     * @param null $description
     * @param null $uri
     */
    protected function setApiProblemResponse($statusCode, $name, $description = null, $uri = null)
    {
        $this->response = new ApiProblemResponse(
            new ApiProblem(
                $statusCode,
                $description,
                $uri,
                $name
            )
        );
    }


}
