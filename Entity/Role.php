<?php

namespace Galvesband\TraUserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Role
 * @package Galvesband\TraUserBundle\Entity
 * @ORM\Entity(repositoryClass="Galvesband\TraUserBundle\Entity\RoleRepository")
 * @ORM\Table(name="tra_role")
 */
class Role {
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
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $role;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="roles")
     */
    private $groups;

    public function __construct() {
        $this->groups = new ArrayCollection();
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
     * @return Role
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
     * Set role
     *
     * @param string $role
     *
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Role
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
     * Add group
     *
     * @param \Galvesband\TraUserBundle\Entity\Group $group
     *
     * @return Role
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
