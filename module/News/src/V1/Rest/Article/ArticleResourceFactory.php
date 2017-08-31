<?php
namespace News\V1\Rest\Article;

class ArticleResourceFactory
{
    public function __invoke($services)
    {
        return new ArticleResource();
    }
}
