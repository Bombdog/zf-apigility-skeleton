<?php
namespace OdmQuery\Factory;

use Interop\Container\ContainerInterface;
use OdmAuth\Request\Request;
use OdmQuery\Service\ApiQueryManager;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Build a fully contextualized API query from the request, including any scope-related restrictions on
 * querying.
 */
class ApiQueryManagerFactory implements FactoryInterface
{
    /**
     * Create query manager service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return ApiQueryManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var Request $request */
        $request = $container->get('Request');

        # request must be http
        if(!$request instanceof Request) {
            throw new \Exception("Cannot use query manager outside HTTP");
        }

        # create manager
        $apiQueryManager = new ApiQueryManager($request->getPagedQuery());

        # read any scope restrictions
        $scopes = $request->getTargetScopeForRequestMethod();







        // $metadata = $dm->getMetadataFactory()->getAllMetadata();






        return $apiQueryManager;

        /*

        /** @var Request $request *
        $request = $container->get('Request');

        # just return an empty query if the request is not an http request
        if(!$request instanceof Request) {
            return $apiQuery;
        }

        # @ todo: make default page size configurable
        $defaultPageSize = 25;

        # apply page and size
        $apiQuery->setPage($request->getQuery('page', 1))
                ->setPageSize($request->getQuery('pageSize', $defaultPageSize));

        # use either a preset query or a supplied query
        $preset = $request->getQuery('preset');

        if($preset === null) {

            $fields = $request->getQuery('fields', []);
            if(!empty($fields) && is_string($fields)) {
                $fields = explode(',',$fields);
            }

            $apiQuery->setFields($fields)
                ->setFilter($request->getQuery('filter', []))
                ->setSort($request->getQuery('sort', []));
        }
        else {
            $apiQuery->setPreset($preset);
            if($preset = PresetFactory::getInstance($preset)){
                $apiQuery->setFields($preset->getFields())
                    ->setFilter($preset->getFilter())
                    ->setSort($preset->getSort());
            }
        }

        return $apiQuery;
        */

    }
}
