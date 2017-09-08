<?php

namespace OdmAuth\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Document\Repository\Oauth\AccessTokenRepository;
use Documents\UserRepository;
use Entity\Document\OAuth\AccessToken;
use Entity\Document\User;
use Entity\Util\Password;
use Zend\Http\Response as HttpResponse;
use OdmAuth\Request\Request as HttpRequest;
use Zend\Http\Response;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * This service is used directly (/oauth endpoints) or wrapped in a ZF\MvcAuth\Authentication
 * authentication adapter for use in DefaultAuthenticationListener .
 *
 * Basic features: only to allowing password grants via the users collection
 *
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
     * Last accessed token
     * @var AccessToken
     */
    protected $token;

    /**
     * @var bool
     */
    protected $error = false;

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
     * Response may be passed in to be modified rather than generated.
     *
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        $this->error = $this->response->getStatusCode() > 299;
    }

    /**
     * Get / initialize the response object
     *
     * @return HttpResponse
     */
    public function getResponse()
    {
        if ($this->response === null) {
            $this->response = new HttpResponse();
            $headers = $this->response->getHeaders();
            $headers->addHeaderLine('Content-type', 'application/json');
            $this->error = false;
        }

        return $this->response;
    }

    /**
     * Get expiry time (token ttl)
     *
     * @return int
     */
    public function getExpiryTime()
    {
        return $this->expiryTime;
    }

    /**
     * Set expiry time, i.e. the ttl of the token
     *
     * @param int $expiryTime
     *
     * @return $this
     */
    public function setExpiryTime($expiryTime)
    {
        $this->expiryTime = $expiryTime;

        return $this;
    }

    /**
     * If the response is going to be an error.
     * @return bool
     */
    public function isError()
    {
        return $this->error;
    }

    /**
     * Handle a request for an access token (grant)
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
     * Check an api resource request, ie one with the Authorization header
     *
     * @param HttpRequest $request
     * @param null $scope
     *
     * @return bool
     */
    public function verifyResourceRequest(HttpRequest $request, $scope = null)
    {
        $this->token = $this->getAccessTokenData($request);

        // Check if we have valid token data
        if ($this->token === null || $this->error) {
            return false;
        }

        /**
         * Check scope, if provided
         * If token doesn't have a scope, it's null/empty, or it's insufficient, then throw 403
         * @see http://tools.ietf.org/html/rfc6750#section-3.1
         *
        if ($scope && (!isset($token["scope"]) || !$token["scope"] || !$this->scopeUtil->checkScope($scope, $token["scope"]))) {
            $response->setError(403, 'insufficient_scope', 'The request requires higher privileges than provided by the access token');
            $response->addHttpHeaders(array(
                'WWW-Authenticate' => sprintf('%s realm="%s", scope="%s", error="%s", error_description="%s"',
                    $this->tokenType->getTokenType(),
                    $this->config['www_realm'],
                    $scope,
                    $response->getParameter('error'),
                    $response->getParameter('error_description')
                )
            ));

            return false;
        }*/

        return (bool) $this->token;
    }

    /**
     * Get the access token
     * @return AccessToken
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Procedure to query the db for an access token.
     * NB if a token is found it will be returned. It may be expired, so check the error flag.
     * Adapted from bshaffer/oauth2-server-php (OAuth2\Controller\TokenController)
     *
     * @param HttpRequest $request
     *
     * @return AccessToken|null
     */
    private function getAccessTokenData(HttpRequest $request)
    {
        $tokenKey = $request->getAccessToken();

        if(!empty($tokenKey)) {
            /** @var AccessTokenRepository $repo */
            $repo = $this->dm->getRepository('Entity\Document\OAuth\AccessToken');

            /** @var AccessToken $token */
            $token = $repo->findOneBy(['accessToken' => $tokenKey]);

            if ($token === null) {
                $this->setApiProblemResponse(401, 'invalid_token', 'The access token provided is invalid');
            } elseif (time() > $token->getExpires()) {
                $this->setApiProblemResponse(401, 'invalid_token', 'The access token provided has expired');
            }

            return $token;
        }

        return null;
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
            $this->setApiProblemResponse(400, 'invalid_request', 'Failed to parse the grant request body');

            return null;
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
            $this->token = $token;

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
    private function setApiProblemResponse($statusCode, $name, $description = null, $uri = null)
    {
        $this->error = true;

        # Use an Apigility ApiProblemResponse for quick format of the output
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
