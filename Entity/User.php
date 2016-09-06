<?php

namespace Galvesband\TraUserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

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
 * @ORM\UniqueEntity(fields="name", message="Username already taken")
 * @ORM\UniqueEntity(fields="email", message="Email already in use")
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
     * @ORM\Column(type="string", length=255, unique=true)
     * @ORM\Assert\NotBlank()
     * @ORM\Assert\Length(max=255, min=4)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * Holds the plain password. Not a column.
     *
     * @var string
     * @Assert\NotBlank()
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
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    private $salt;

    /**
     * @var bool
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
     * @ORM\JoinTable(name="tra_users_groups")
     */
    private $groups;

    public function __construct()
    {
        $this->isActive = true;
        // BCrypt doesn't need salting... So this is mostly useless.
        $this->refreshSalt();
        $this->groups = new ArrayCollection();
    }

    public function refreshSalt()
    {
        $this->salt = md5(uniqid(null, true));
    }

    // Implementation of UserInterface

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize() {
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
    public function unserialize($serialized) {
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
    public function getRoles() {
        $roles = [];
        foreach ($this->getGroups() as $group) {
            foreach ($group->getRoles() as $role) {
                if (!isset($roles[$role->getRole()])) {
                    $roles[$role->getRole()] = $role->getRole();
                }
            }
        }

        $roles = array_values($roles);
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
    public function getUsername() {
        return $this->name;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials() {
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
    public function isAccountNonExpired() {
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
    public function isAccountNonLocked() {
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
    public function isCredentialsNonExpired() {
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
    public function isEnabled() {
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
    public function getPassword() {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt() {
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
        $this->refreshSalt();
        $this->password = '';

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
    public function addGroup(\Galvesband\TraUserBundle\Entity\Group $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param \Galvesband\TraUserBundle\Entity\Group $group
     */
    public function removeGroup(\Galvesband\TraUserBundle\Entity\Group $group)
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
}
