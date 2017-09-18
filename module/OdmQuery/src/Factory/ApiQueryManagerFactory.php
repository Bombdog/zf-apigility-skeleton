<?php
namespace OdmQuery\Factory;

use Doctrine\ODM\MongoDB\DocumentManager;
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
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var Request $request */
        $request = $container->get('Request');

        # request must be http
        if(!$request instanceof Request) {
            throw new \Exception("Cannot invoke the query manager service without an HTTP request");
        }

        $apiQuery = $request->getPagedQuery();
        $scopes = $request->getTargetScopeSetForRequestMethod();

        $qm = new ApiQueryManager($apiQuery, $scopes->getMatches());

        /** @var DocumentManager $dm */
        $dm = $dm = $container->get('doctrine.documentmanager.odm_default');
        $metadata = $dm->getMetadataFactory()->getAllMetadata();
        $qm->setClassMetadata($metadata[0]);
        // $qm->setOrderByManager($container->get('ZfDoctrineQueryBuilderOrderByManagerOdm'););
        // $qm->setFilterManager($container->get('ZfDoctrineQueryBuilderFilterManagerOdm'));

        return $qm;
    }
}
