<?php

namespace OdmAuth\Adapter;

use Application\Cache\ObjectCache;
use OAuth2\Response as OAuth2Response;
use OdmAuth\Service\OdmAuthService;
use Zend\Http\Request;
use Zend\Http\Response;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\MvcAuth\Authentication\AbstractAdapter;
use ZF\MvcAuth\Identity;
use ZF\MvcAuth\MvcAuthEvent;

/**
 * Modified OAuth2 Adapter to allow a customised identity.
 * Class OAuth2Adapter
 * @package Application\Auth
 */
class OdmAdapter extends AbstractAdapter
{
    /**
     * Redis based cache for faster token processing
     * @todo: Should be moved to the repository instead
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
     * @var OdmAuthService
     */
    private $oauth2Service;

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
     * @param OdmAuthService $oauth2Service
     */
    public function __construct(OdmAuthService $oauth2Service, $types = null)
    {
        $this->oauth2Service = $oauth2Service;

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
     * @return mixed|Response|Identity\AuthenticatedIdentity|Identity\GuestIdentity
     * @throws \Exception
     */
    public function authenticate(Request $request, Response $response, MvcAuthEvent $mvcAuthEvent)
    {
        if(! $request instanceof \OdmAuth\Request\Request) {
            return new ApiProblemResponse(new ApiProblem(500,'Server misconfigured - OdmAdapter needs an OdmAuth request'));
        }

        /** @var \OdmAuth\Request\Request $request */
        $requestTokenValue = $request->getAccessToken();

        # Try the cache for an identity rather than hitting the db
        if (!empty($requestTokenValue) && $this->cache !== null) {
            $this->cache->setNamespace('Bearer');
            if ($this->cache->contains($requestTokenValue)) {
                $identity = unserialize($this->cache->fetch($requestTokenValue));
                if ($identity !== false) {
                    return $identity;
                }
            }
        }

        # Verify the request
        if ($this->oauth2Service->verifyResourceRequest($request)) {

            $token = $this->oauth2Service->getToken();
            //$token['user'] = $this->getUserDetails($token['user_id']);
            $identity = new Identity\AuthenticatedIdentity($token);  //@todo pad out identity with user data
            //$identity->setName($token['user_id']);

        } else {

            if($this->oauth2Service->isError()) {
                return $this->oauth2Service->getResponse();
            }

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
        $store = $this->oauth2Service->getStorage('access_token');

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
}