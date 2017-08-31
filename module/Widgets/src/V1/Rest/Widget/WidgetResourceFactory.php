<?php
namespace Widgets\V1\Rest\Widget;

class WidgetResourceFactory
{
    public function __invoke($services)
    {
        return new WidgetResource();
    }
}
