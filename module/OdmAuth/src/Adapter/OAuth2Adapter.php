<?php

namespace OdmAuth\Adapter;

use Application\Cache\ObjectCache;
use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use OAuth2\Server as OAuth2Server;
use Zend\Http\Request;
use Zend\Http\Response;
use ZF\MvcAuth\Authentication\AbstractAdapter;
use ZF\MvcAuth\Identity;
use ZF\MvcAuth\MvcAuthEvent;

/**
 * Modified OAuth2 Adapter to allow a customised identity.
 * Class OAuth2Adapter
 * @package Application\Auth
 */
class OAuth2Adapter extends AbstractAdapter
{
    /**
     * Redis based cache for faster token processing
     *
     * @var ObjectCache
     */
    protected $cache;

    /**
     * Authorization header token types this adapter can fulfill.
     *
     * @var array
     */
    protected $authorizationTokenTypes = ['bearer'];

    /**
     * @var OAuth2Server
     */
    private $oauth2Server;

    /**
     * Authentication types this adapter provides.
     *
     * @var array
     */
    private $providesTypes = ['oauth2'];

    /**
     * Request methods that will not have request bodies
     *
     * @var array
     */
    private $requestsWithoutBodies = [
        'GET',
        'HEAD',
        'OPTIONS',
    ];

    /**
     * @param OAuth2Server $oauth2Server
     */
    public function __construct(OAuth2Server $oauth2Server, $types = null)
    {
        $this->oauth2Server = $oauth2Server;

        if (is_string($types) && !empty($types)) {
            $types = [$types];
        }

        if (is_array($types)) {
            $this->providesTypes = $types;
        }
    }

    /**
     * Set an optional cache for speeding up token (identity) loading.
     *
     * @param ObjectCache $cache
     */
    public function setCache(ObjectCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return array Array of types this adapter can handle.
     */
    public function provides()
    {
        return $this->providesTypes;
    }

    /**
     * Attempt to match a requested authentication type
     * against what the adapter provides.
     *
     * @param string $type
     *
     * @return bool
     */
    public function matches($type)
    {
        return in_array($type, $this->providesTypes, true);
    }

    /**
     * Determine if the given request is a type (oauth2) that we recognize
     *
     * @param Request $request
     *
     * @return false|string
     */
    public function getTypeFromRequest(Request $request)
    {
        $type = parent::getTypeFromRequest($request);

        if (false !== $type) {
            return 'oauth2';
        }

        if (!in_array($request->getMethod(), $this->requestsWithoutBodies)
            && $request->getHeaders()->has('Content-Type')
            && $request->getHeaders()->get('Content-Type')->match('application/x-www-form-urlencoded')
            && $request->getPost('access_token')
        ) {
            return 'oauth2';
        }

        if (null !== $request->getQuery('access_token')) {
            return 'oauth2';
        }

        return false;
    }

    /**
     * Perform pre-flight authentication operations.
     *
     * Performs a no-op; nothing needs to happen for this adapter.
     *
     * @param Request $request
     * @param Response $response
     *
     * @return void
     */
    public function preAuth(Request $request, Response $response)
    {
    }

    /**
     * Attempt to authenticate the current request.
     *
     * @param Request $request
     * @param Response $response
     * @param MvcAuthEvent $mvcAuthEvent
     *
     * @return false|Identity\IdentityInterface False on failure, IdentityInterface
     *     otherwise
     */
    public function authenticate(Request $request, Response $response, MvcAuthEvent $mvcAuthEvent)
    {
        $oauth2request = new OAuth2Request(
            $request->getQuery()->toArray(),
            $request->getPost()->toArray(),
            [],
            ($request->getCookie() ? $request->getCookie()->getArrayCopy() : []),
            ($request->getFiles() ? $request->getFiles()->toArray() : []),
            (method_exists($request, 'getServer') ? $request->getServer()->toArray() : $_SERVER),
            $request->getContent(),
            $request->getHeaders()->toArray()
        );

        # Read the authorization header data in order to access the cache
        $requestTokenKey = null;
        $requestTokenValue = null;
        $authHeader = $request->getHeader('Authorization');
        if ($authHeader !== false) {
            $tokenData = explode(' ', $authHeader->getFieldValue());
            # Only interested in Bearer tokens at the moment
            if ($tokenData[0] == 'Bearer') {
                list($requestTokenKey, $requestTokenValue) = $tokenData;
            }
        }

        # Try the cache for an identity
        if ($requestTokenValue !== null && $this->cache !== null) {
            $this->cache->setNamespace($requestTokenKey);
            if ($this->cache->contains($requestTokenValue)) {
                $identity = unserialize($this->cache->fetch($requestTokenValue));
                if ($identity !== false) {
                    return $identity;
                }
            }
        }

        # Verify the request
        if ($this->oauth2Server->verifyResourceRequest($oauth2request)) {

            $token = $this->oauth2Server->getAccessTokenData($oauth2request);
            $token['user'] = $this->getUserDetails($token['user_id']);
            $identity = new Identity\AuthenticatedIdentity($token);
            $identity->setName($token['user_id']);
        } else {

            /** @var OAuth2Response $oauth2Response */
            $oauth2Response = $this->oauth2Server->getResponse();
            $status = $oauth2Response->getStatusCode();

            // 401 or 403 mean invalid credentials or unauthorized scopes; report those.
            if (in_array($status, [401, 403], true) && null !== $oauth2Response->getParameter('error')) {
                return $this->mergeOAuth2Response($status, $response, $oauth2Response);
            }

            // Merge in any headers; typically sets a WWW-Authenticate header.
            $this->mergeOAuth2ResponseHeaders($response, $oauth2Response->getHttpHeaders());

            // Otherwise, no credentials were present at all, so we just create a guest identity.
            $identity = new Identity\GuestIdentity();
        }

        # Cache the identity (for ten minutes)
        if ($requestTokenValue !== null && $this->cache !== null) {
            $this->cache->save($requestTokenValue, serialize($identity), 600);
        }

        return $identity;
    }

    /**
     * Get the full user details from oauth storage.
     */
    private function getUserDetails($user_id)
    {
        /** @var MongoAdapter $store */
        $store = $this->oauth2Server->getStorage('access_token');

        if ($store !== null) {
            $user = $store->getUser($user_id);
            $user['_id'] = (string) $user['_id'];
            unset($user['scope']);
            unset($user['password']);
            unset($user['createdAt']);
            unset($user['username']);
            unset($user['userProfile']);
            if (isset($user['auctioneers'])) {
                foreach ($user['auctioneers'] as $key => $value) {
                    $user['auctioneers'][$key] = (string) $value;
                }
            }
            return $user;
        }
        return [];
    }

    /**
     * Merge the OAuth2\Response instance's status and headers into the current Zend\Http\Response.
     *
     * @param int $status
     * @param Response $response
     * @param OAuth2Response $oauth2Response
     *
     * @return Response
     */
    private function mergeOAuth2Response($status, Response $response, OAuth2Response $oauth2Response)
    {
        $response->setStatusCode($status);
        return $this->mergeOAuth2ResponseHeaders($response, $oauth2Response->getHttpHeaders());
    }

    /**
     * Merge the OAuth2\Response headers into the current Zend\Http\Response.
     *
     * @param Response $response
     * @param array $oauth2Headers
     *
     * @return Response
     */
    private function mergeOAuth2ResponseHeaders(Response $response, array $oauth2Headers)
    {
        if (empty($oauth2Headers)) {
            return $response;
        }

        $headers = $response->getHeaders();
        foreach ($oauth2Headers as $header => $value) {
            $headers->addHeaderLine($header, $value);
        }

        return $response;
    }
}
