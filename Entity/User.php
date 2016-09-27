<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Galvesband\TraUserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class User
 *
 * Password hashing:
 *   This is how it works.
 *
 *   When you want to create a new user or update the password of an
 *   old one, you just need to set the new one through the
 *   setPlainPassword() method. It will internally set that plain
 *   password into a private member variable not mapped to the database.
 *
 *   The bundle registers a couple of event listeners that wait for
 *   doctrine's prePersist and preUpdate. If the persisted entity
 *   is an user, it will use
 *   TraUserBundle\DependencyInjection\UserManager::updateUser()
 *   to hash the plain password with the encoder configured in
 *   the security file.
 *
 * @package Galvesband\TraUserBundle\Entity
 * @ORM\Entity(repositoryClass="Galvesband\TraUserBundle\Entity\UserRepository")
 * @UniqueEntity(fields="name", message="Username already taken")
 * @ORM\Table(name="tra_user")
 */
class User implements AdvancedUserInterface, \Serializable {
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=128, min=4)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * Holds the plain password. Not a column.
     *
     * @var string
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * Hashed password.
     *
     * Length 64 works well with bcrypt.
     *
     * @var string
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank(message="The password can not be empty.")
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     * @Assert\NotBlank()
     */
    private $salt;

    /**
     * @var bool
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var bool
     * @ORM\Column(name="is_super_admin", type="boolean")
     */
    private $isSuperAdmin;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
     * @ORM\JoinTable(name="tra_users_groups")
     */
    private $groups;

    /**
     * @ORM\OneToOne(targetEntity="ResetToken", inversedBy="user")
     * @ORM\JoinColumn(name="token_id", referencedColumnName="id")
     */
    private $token;

    public function __construct()
    {
        $this->isActive = true;
        $this->isSuperAdmin = false;
        $this->groups = new ArrayCollection();
        $this->salt = null;
        // An empty value by default will make the UserManager to not update the password field.
        $this->plainPassword = null;
    }

    // Implementation of UserInterface

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->name,
            $this->password,
            $this->salt,
            $this->isActive,
        ]);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->name,
            $this->password,
            $this->salt,
            $this->isActive,
            ) = unserialize($serialized);
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     * @return array (string) The user roles
     */
    public function getRoles()
    {
        $roles = [];
        if ($this->getIsSuperAdmin()) {
            $roles[] = 'ROLE_SUPER_ADMIN';
        }
        foreach ($this->getGroups() as $group) {
            $groupRoles = $group->getRoles();
            /** @var Role $role */
            foreach ($groupRoles as $role) {
                if (false === array_search($role->getRole(), $roles)) {
                    $roles[] = $role->getRole();
                }
            }
        }

        if (count($roles) === 0) {
            $roles[] = 'ROLE_USER';
        }

        return $roles;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->name;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = '';
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    // Doctrine getters and setters

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Gets the plain-form password.
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Sets a plain password
     *
     * @param $password string
     * @return $this
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        // Changing some mapped values so preUpdate will get called.
        $this->setPassword('this will be updated later with plain password hashed');
        $this->setSalt('Same');

        return $this;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Add group
     *
     * @param \Galvesband\TraUserBundle\Entity\Group $group
     *
     * @return User
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param \Galvesband\TraUserBundle\Entity\Group $group
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function hasRole($role)
    {
        return (false !== array_search($role, $this->getRoles()));
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set token
     *
     * @param \Galvesband\TraUserBundle\Entity\ResetToken $token
     *
     * @return User
     */
    public function setToken(ResetToken $token = null)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return \Galvesband\TraUserBundle\Entity\ResetToken
     */
    public function getToken()
    {
        return $this->token;
    }



    /**
     * Set isSuperAdmin
     *
     * @param boolean $isSuperAdmin
     *
     * @return User
     */
    public function setIsSuperAdmin($isSuperAdmin)
    {
        $this->isSuperAdmin = $isSuperAdmin;

        return $this;
    }

    /**
     * Get isSuperAdmin
     *
     * @return boolean
     */
    public function getIsSuperAdmin()
    {
        return $this->isSuperAdmin;
    }
}
