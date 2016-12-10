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

/**
 * Class Group
 * @package Galvesband\TraUserBundle\Entity
 * @ORM\Entity(repositoryClass="Galvesband\TraUserBundle\Entity\GroupRepository")
 * @ORM\Table(name="tra_group")
 */
class Group {
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=100, min=4)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="User", mappedBy="groups")
     */
    private $users;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="groups")
     * @ORM\JoinTable(name="tra_groups_roles")
     */
    private $roles;

    public function __construct() {
        $this->users = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

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
     * @return Group
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
     * Set description
     *
     * @param string $description
     *
     * @return Group
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add user
     *
     * @param \Galvesband\TraUserBundle\Entity\User $user
     *
     * @return Group
     */
    public function addUser(\Galvesband\TraUserBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Galvesband\TraUserBundle\Entity\User $user
     */
    public function removeUser(\Galvesband\TraUserBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add role
     *
     * @param \Galvesband\TraUserBundle\Entity\Role $role
     *
     * @return Group
     */
    public function addRole(\Galvesband\TraUserBundle\Entity\Role $role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Remove role
     *
     * @param \Galvesband\TraUserBundle\Entity\Role $role
     */
    public function removeRole(\Galvesband\TraUserBundle\Entity\Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * True if this group has assigned a given role name.
     *
     * @param $roleName string For example, 'ROLE_SUPER_ADMIN'
     * @return bool
     */
    public function hasRole($roleName)
    {
        foreach ($this->getRoles() as $role) {
            if ($role->getRole() === $roleName) {
                return true;
            }
        }

        return false;
    }

    public function __toString() {
        return $this->getName();
    }
}
