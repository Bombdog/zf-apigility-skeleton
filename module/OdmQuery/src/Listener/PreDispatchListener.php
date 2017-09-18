<?php

namespace OdmQuery\Listener;

use OdmAuth\Request\Request;
use OdmQuery\Query\ApiQuery;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ResponseInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * This is a custom listener that pulls in query parameters from the request
 * and build's an ApiQuery object, this is then appended to the request.
 */
class PreDispatchListener
{
    /**
     *
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

        $scopesForMethod = $request->getTargetScopeSetForRequestMethod();
        $scopeMatch = $scopesForMethod->getMatches();

        if (empty($scopeMatch)) {
            return $this->createProblemResponse($mvcEvent, 403, 'Query scope error',
                'Attempt to create a query without scope');
        }

        $routeMatch = $mvcEvent->getRouteMatch();

        /** @var ApiQuery $apiQuery */
        $apiQuery = $sm->get('OdmQuery\Query\ApiQuery');

        # validate the preset used (if any)
        $preset = $apiQuery->getPreset();
        if ($preset !== null) {
            $allowedPresets = $routeMatch->getParam('allowedPresets', []);
            if (array_search($preset, $allowedPresets) === false) {
                return $this->createProblemResponse($mvcEvent, 403, 'forbidden','Query preset not available');
            }
        }

        $request->setPagedQuery($apiQuery);
    }

    /**
     * @param MvcEvent $mvcEvent
     * @param int $status
     * @param string $detail
     * @param string $title
     *
     * @return mixed
     */
    private function createProblemResponse($mvcEvent, $status, $title, $detail)
    {
        $mvcEvent->stopPropagation();
        $mvcEvent->setResponse(new ApiProblemResponse(
            new ApiProblem($status, $detail, null, $title)
        ));

        return $mvcEvent->getResponse();
    }
}
