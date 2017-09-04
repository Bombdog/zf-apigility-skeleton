<?php
namespace Fixture\V1\Rpc\Apply;

class ApplyControllerFactory
{
    public function __invoke($controllers)
    {
        return new ApplyController();
    }
}
