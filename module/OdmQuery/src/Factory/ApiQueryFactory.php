<?php
namespace OdmQuery\Factory;

use Interop\Container\ContainerInterface;
use OdmAuth\Request\Request;
use OdmQuery\Query\ApiQuery;
use OdmQuery\Query\PresetFactory;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Build an API query from an HTTP request.
 * This factory provides a query from the user's own request, can then be screened for
 * security purposes by applying any scope related restrictions.
 *
 */
class ApiQueryFactory implements FactoryInterface
{
    private static $apiQuery;

    /**
     * Create an apiQuery based on the request.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return ApiQuery
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if(self::$apiQuery === null) {

            $apiQuery = new ApiQuery();

            /** @var Request $request */
            $request = $container->get('Request');

            # just return an empty query if the request is not an http request
            if (!$request instanceof Request) {
                return $apiQuery;
            }

            # @ todo: make default page size configurable
            $defaultPageSize = 25;

            # apply page and size
            $apiQuery->setPage($request->getQuery('page', 1))
                ->setPageSize($request->getQuery('pageSize', $defaultPageSize));

            # use either a preset query or a supplied query
            $preset = $request->getQuery('preset');

            if ($preset === null) {

                $fields = $request->getQuery('fields', []);
                if (!empty($fields) && is_string($fields)) {
                    $fields = explode(',', $fields);
                }

                $apiQuery->setFields($fields)
                    ->setFilter($request->getQuery('filter', []))
                    ->setSort($request->getQuery('sort', []));
            } else {
                $apiQuery->setPreset($preset);
                if ($preset = PresetFactory::getInstance($preset)) {
                    $apiQuery->setFields($preset->getFields())
                        ->setFilter($preset->getFilter())
                        ->setSort($preset->getSort());
                }
            }

            self::$apiQuery = $apiQuery;
        }

        return self::$apiQuery;
    }
}
