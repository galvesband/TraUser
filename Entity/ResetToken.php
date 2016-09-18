<?php

namespace Galvesband\TraUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class ResetToken
 * @package Galvesband\TraUserBundle\Entity
 * @ORM\Entity(repositoryClass="Galvesband\TraUserBundle\Entity\ResetTokenRepository")
 * @UniqueEntity(fields="user_id", message="User already has a token")
 * @ORM\Table(name="tra_user_reset_token")
 */
class ResetToken
{
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="User", mappedBy="token")
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $token;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->token = bin2hex(random_bytes(64));
        $this->setCreatedAt(new \DateTime('now'));
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
     * Set token
     *
     * @param string $token
     *
     * @return ResetToken
     */
    public function setToken($token = null)
    {
        if (!is_null($token)) {
            $this->token = $token;
        } else {
            $this->token = bin2hex(random_bytes(64));
        }

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ResetToken
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param \Galvesband\TraUserBundle\Entity\User $user
     *
     * @return ResetToken
     */
    public function setUser(\Galvesband\TraUserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Galvesband\TraUserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
