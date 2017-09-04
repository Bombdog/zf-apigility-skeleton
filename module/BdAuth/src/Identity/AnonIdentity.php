<?php
namespace BdAuth\Identity;

use Zend\Permissions\Rbac\AbstractRole as AbstractRbacRole;
use ZF\MvcAuth\Identity\IdentityInterface;

class AnonIdentity extends AbstractRbacRole implements IdentityInterface
{
    const USER_ANONYMOUS_ROLE = 1;

    protected $identity;

    public function __construct($scope)
    {
        $this->identity = [
            'user_id' => 0,
            'scope'   => $scope
        ];
    }

    public function getRoleId()
    {
        return self::USER_ANONYMOUS_ROLE;
    }

    public function getAuthenticationIdentity()
    {
        return $this->identity;
    }
}
