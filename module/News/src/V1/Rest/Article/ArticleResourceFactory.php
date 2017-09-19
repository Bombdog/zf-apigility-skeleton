<?php

namespace News\V1\Rest\Article;

use Entity\Document\Article;
use Entity\Hydrator\ArticleHydrator;
use Zend\ServiceManager\ServiceManager;

class ArticleResourceFactory
{
    /**
     * @param ServiceManager $services
     *
     * @return ArticleResource
     */
    public function __invoke($services)
    {
        $dm = $services->get('doctrine.documentmanager.odm_default');
        $hydrator = new ArticleHydrator($dm);
        $resource = new ArticleResource($dm, $hydrator);
        $resource->setEntityClass(Article::class);
        $resource->setQueryManager($services->get('QueryManager'));


        // dump($resource->getCollectionClass());

        return $resource;
    }
}
