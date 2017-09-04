<?php
namespace News\V1\Rest\Article;

use Entity\Document\Article;
use Entity\Hydrator\ArticleHydrator;

class ArticleResourceFactory
{
    public function __invoke($services)
    {
        $dm = $services->get('doctrine.documentmanager.odm_default');

        $resource =  new ArticleResource();
        $resource->setEntityClass(Article::class);
        $resource->setObjectManager($dm);
        $resource->setHydrator(new ArticleHydrator($dm));

        return $resource;
    }
}
