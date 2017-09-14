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
 * This is a custom listener that pulls in any query parameters from the request.
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

        /** @var ApiQuery $apiQuery */
        $apiQuery = $sm->get('OdmQuery\Query\ApiQuery');

        # validate the preset used (if any)
        $preset = $apiQuery->getPreset();
        if($preset !== null) {
            $allowedPresets = $routeMatch->getParam('allowedPresets', []);
            if(array_search($preset,$allowedPresets) === false) {
                $mvcEvent->stopPropagation();
                $mvcEvent->setResponse(new ApiProblemResponse(
                    new ApiProblem(403, 'Preset not available', null,'forbidden')
                ));

                return $mvcEvent->getResponse();
            }
        }

        $request->setPagedQuery($apiQuery);
    }
}
