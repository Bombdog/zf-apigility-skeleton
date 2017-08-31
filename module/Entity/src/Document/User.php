<?php

namespace Entity\Document;

use Entity\Api\OAuth\Role;
use Entity\Util\Password;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Entity\Document\Traits\CreatedStampTrait;
use Entity\Document\Traits\SequenceTrait;
use Entity\EntityInvalidArgumentException;

/**
 * A user entity that has fields compatible with OAuth2 server.
 * @ODM\Document(
 *     collection="users",
 *     repositoryClass="Entity\Repository\UserRepository"
 * )
 * @ODM\Indexes({
 *   @ODM\Index(keys={"sequence"="asc"},unique=true,name="idxUserSequence"),
 *   @ODM\Index(keys={"username"="asc"},unique=true,name="idxUserUsername"),
 * })
 * @author Andy H
 */
class User
{
    use SequenceTrait {
        setSequence as setSequenceTrait;
    }
    use CreatedStampTrait;

    /**
     * @ODM\Id
     * @var string
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     * @var string
     */
    protected $username;

    /**
     * Use a suitable encryption for this, e.g Bcrypt.
     * @ODM\Field(type="string")
     * @var string
     */
    protected $password;

    /**
     * @ODM\Field(type="int")
     * @var int
     */
    protected $role;

    /**
     * OAuth2 server field - First name.
     * @ODM\Field(type="string", name="first_name")
     * @var string
     */
    protected $firstName;

    /**
     * OAuth2 server field - Last name.
     * @ODM\Field(type="string", name="last_name")
     * @var string
     */
    protected $lastName;

    /**
     * OAuth2 server field - scope
     * @ODM\Field(type="string")
     * @var string
     */
    protected $scope;

    /**
     * OAuth2 server field - email
     * @ODM\Field(type="string")
     * @var string
     */
    protected $email;

    /**
     * OAuth2 server field - email_verified
     * @ODM\Field(type="boolean", name="email_verified")
     * @var bool
     */
    protected $emailVerified = false;

    /**
     * A string for password resets
     * @var string
     * @ODM\Field(type="string")
     */
    protected $resetKey;

    /**
     * Autioneers association. One user may administer on behalf of many auctioneers.
     * This one-to-many connection is complementary to the lots:control scope
     * @var ArrayCollection
     * @ODM\ReferenceMany(targetDocument="Entity\Document\Auctioneer", simple=true)
     */
    protected $auctioneers;

    /**
     * Flag to show that a user account is enabled.
     * @ODM\Field(type="boolean")
     * @var bool
     */
    protected $enabled = false;

    /**
     * Generic array of flags. Declare a constant for each, e.g. FLAG_EMAIL_OPT_IN
     * @ODM\Field(type="hash")
     * @var array
     */
    protected $flags = [];

    /**
     * Constructor to initialise collections.
     */
    public function __construct()
    {
        $this->auctioneers = new ArrayCollection();
        $this->userProfile = new UserProfile();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the user sequence (a.k.a userId)
     * NB this "overrides" the sequence trait to enforce a 1:1 link with user profile.
     *
     * @param int $value
     *
     * @return $this
     */
    public function setSequence($value)
    {
        $this->setSequenceTrait($value);
        $this->userProfile->setUserId($this->sequence);
        return $this;
    }

    /**
     * Username is the same as email, although it's stored twice for compatibility
     * with oauth server schema.
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = strtolower($username);
        $this->setEmail($username);
        return $this;
    }

    /**
     * Email is the same as username.
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $email = strtolower($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new EntityInvalidArgumentException("'$email' is not a valid email");
        }
        $this->email = $email;
        return $this;
    }

    /**
     * Is the user's email verified?
     * @return boolean
     */
    public function isEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * Set email verified, user has or has not followed a confirmation link.
     *
     * @param boolean $emailVerified
     *
     * @return $this
     */
    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = (bool) $emailVerified;
        return $this;
    }

    /**
     * Get password.
     * Passwords are always stored and returned as a hash.
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password. Checks the password before hashing it.
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        if (!Password::isBcryptHash($password)) {
            if (!Password::isValidPassword($password)) {
                throw new EntityInvalidArgumentException('Password must be at least 8 characters long, and contain at least 1 number.');
            }
            $password = Password::bcrypt($password);
        }
        $this->password = $password;
        return $this;
    }

    /**
     * Get role.
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set a role for the user.
     *
     * @param int $role
     *
     * @return $this
     */
    public function setRole($role)
    {
        if (!Role::isRole($role)) {
            throw new EntityInvalidArgumentException("$role is not a valid role");
        }
        if (is_string($role) && !ctype_digit($role)) {
            $role = Role::getId($role);
        }
        $this->scope = Role::getScope($role);
        $this->role = $role;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Set the password reset key
     * @return string
     */
    public function getResetKey()
    {
        return $this->resetKey;
    }

    /**
     * Get the password reset key
     *
     * @param string $resetKey
     *
     * @return $this
     */
    public function setResetKey($resetKey)
    {
        $this->resetKey = $resetKey;
        return $this;
    }

    /**
     * Get the scope for this user.
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set the scope for this user (string of scopes separated by spaces).
     *
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Get the auctioneers associated with this user.
     * This is a security credential, it's not the user's favourite seller!
     * @return ArrayCollection
     */
    public function getAuctioneers()
    {
        return $this->auctioneers;
    }

    /**
     * Add an auctioneer to this user's profile (so the user may administer for this auctioneer).
     * N.B. Also needs a corresponding scope in the mysql user table to enable editing in HAPI.
     *
     * @param Auctioneer $auctioneer
     *
     * @return $this
     */
    public function addAuctioneer(Auctioneer $auctioneer)
    {
        if (!$this->auctioneers->contains($auctioneer)) {
            $this->auctioneers->add($auctioneer);
        }
        return $this;
    }

    /**
     * Remove auctioneer from profile.
     *
     * @param Auctioneer $auctioneer
     *
     * @return $this
     */
    public function removeAuctioneer(Auctioneer $auctioneer)
    {
        $this->auctioneers->removeElement($auctioneer);
        return $this;
    }

    /**
     * Convenience method to test if this User is attached to a given auction house.
     *
     * @param Auctioneer $auctioneer
     *
     * @return bool
     */
    public function isUserForAuctionHouse(Auctioneer $auctioneer)
    {
        return $this->auctioneers->contains($auctioneer);
    }

    /**
     * Get the user's profile.
     * @return UserProfile
     */
    public function getUserProfile()
    {
        return $this->userProfile;
    }

    /**
     * Set the user profile.
     *
     * @param UserProfile $userProfile
     *
     * @return $this
     */
    public function setUserProfile($userProfile)
    {
        $this->userProfile = $userProfile;
        return $this;
    }

    /**
     * If the user's account is enabled.
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set the account to enabled or disabled.
     * Note that when disabling accounts, the password must be scrambled to prevent further logins.
     *
     * @param boolean $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $enabled = (bool) $enabled;
        if ($enabled != $this->enabled) {
            $this->enabled = $enabled;
            if (!$enabled) {
                $this->setPassword(substr(md5(rand()), 0, 20) . '*');
            }
        }
        return $this;
    }

    /**
     * Set flag with flagName.
     * If no value is passed it is treated as a boolean true.
     * If null value is passed then the flag is cleared.
     *
     * @param string $flagName
     * @param mixed $value
     *
     * @return $this
     */
    public function setFlag($flagName, $value = true)
    {
        if ($value !== null) {
            if (!is_scalar($value)) {
                throw new EntityInvalidArgumentException("User flags must be scalar values");
            }
            $this->flags[$flagName] = $value;
        } else {
            unset($this->flags[$flagName]);
        }
        return $this;
    }

    /**
     * Clear (unset) flag with flagName.
     * This removes the flag from the record.
     *
     * @param string $flagName
     *
     * @return $this
     */
    public function unsetFlag($flagName)
    {
        return $this->setFlag($flagName, null);
    }

    /**
     * Get the value of a specific flag.
     *
     * @param $flagName
     *
     * @return mixed
     */
    public function getFlag($flagName)
    {
        if (isset($this->flags[$flagName])) {
            return $this->flags[$flagName];
        }
        return false;
    }

    /**
     * Get all flags for this user.
     * @return array
     */
    public function getFlags()
    {
        return $this->flags;
    }
}
