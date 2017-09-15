<?php
namespace OdmScope\Listener;

use Entity\Document\OAuth\AccessToken;
use OdmAuth\Request\Request;
use OdmScope\Scope\TargetScope;
use OdmScope\Service\ScopeService;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ResponseInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\MvcAuth\Identity\IdentityInterface;

/**
 * This is a custom listener that pulls in the configured scopes for the requested route.
 */
class PreDispatchListener
{
    /**
     * Determine if we have an authorization failure, and, if so, return a 403 response
     *
     * @param MvcEvent $mvcEvent
     *
     * @return ResponseInterface|null
     */
    public function __invoke(MvcEvent $mvcEvent)
    {
        /** @var ServiceManager $sm */
        $sm = $mvcEvent->getApplication()->getServiceManager();

        /** @var Request $request */
        $request = $mvcEvent->getRequest();
        if (!$mvcEvent->getRequest() instanceof Request) {
            return null;
        }

        $routeMatch = $mvcEvent->getRouteMatch();
        $routeName = $routeMatch->getMatchedRouteName();

        # ignore the apigility admin area and any oauth requests
        if ($routeName == 'home' || $routeName == 'oauth' || strncmp($routeName, 'zf-apigility', 12) === 0) {
            return null;
        }

        # read in the target scope from the route match
        $targetScope = new TargetScope($routeMatch->getParam('scope'));
        $targetScope->setRouteName($routeName);
        $targetScope->setReadScope($routeMatch->getParam('readScope', []));
        $targetScope->setWriteScope($routeMatch->getParam('writeScope', []));
        $targetScope->setWriteAllScope($routeMatch->getParam('writeAllScope', []));
        $request->setTargetScope($targetScope);

        # read in the user's scope
        $userScope = null;

        /** @var IdentityInterface $identity */
        $identity = ($sm->has('api-identity')) ? $sm->get('api-identity') : null;

        if($identity !== null) {
            /** @var AccessToken $token */
            $token = $identity->getAuthenticationIdentity();
            $userScope = $token->getScope();
        }

        # determine if targeted scope is accessible before routing
        try {
            if($identity === null) {
                throw new \Exception('Missing identity');
            }

            if($userScope === null) {
                throw new \Exception('Identity without scope');
            }

            /** @var ScopeService $scopeService */
            $scopeService = $sm->get('OdmScope\Service\ScopeService');
            $targetScopeSet = $targetScope->getTargetScopeSetForHttpMethod($request->getMethod());
            $userScopeSet = $scopeService->parseScopeList($userScope);

            dump($targetScopeSet);

            foreach ($targetScopeSet as $target) {
                dump($target);
            }

            dump($userScopeSet);



            $allowed = false;
            foreach ($userScopeSet as $userScope) {

                dump($userScope);

                if($targetScopeSet->contains($userScope)) {
                    $allowed = true;
                    break;
                }
            }

            if(!$allowed) {
                throw new \Exception("Forbidden");
            }
        }
        catch (\Exception $e) {

            $mvcEvent->stopPropagation();
            $mvcEvent->setResponse(new ApiProblemResponse(
                new ApiProblem(403, $e->getMessage(), null,'insufficient_scope')
            ));

            return $mvcEvent->getResponse();
        }
    }
}
