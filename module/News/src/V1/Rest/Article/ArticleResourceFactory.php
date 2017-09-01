<?php
namespace News\V1\Rest\Article;

class ArticleResourceFactory
{
    public function __invoke($services)
    {
        $resource =  new ArticleResource();

        $resource->setEntityClass('Entity\Document\Article');

        $resource->setObjectManager($services->get('doctrine.documentmanager.odm_default'));

        return $resource;



    }
}
