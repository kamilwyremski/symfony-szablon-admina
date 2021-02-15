<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    public function __construct()
    {
        $this->isActive = 0;
        $this->registerIp = '';
        $this->registerDate = new \DateTime();
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     * @Assert\Regex(
     *     pattern     = "/^[a-zA-Z0-9_-]+$/",
     *     message="Your username cannot contain a special chars"
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $activationIp;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activationDate;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $registerIp;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registerDate;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity=ResetPasswordRequest::class, mappedBy="user", cascade={"remove"})
     */
    private $reset_password_requests;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $activationCode;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }


    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function eraseCredentials()
    {
    }

    public function getActivationIp(): ?string
    {
        return $this->activationIp;
    }

    public function setActivationIp(?string $activationIp): self
    {
        $this->activationIp = $activationIp;

        return $this;
    }

    public function getActivationDate(): ?\DateTimeInterface
    {
        return $this->activationDate;
    }

    public function setActivationDate(?\DateTimeInterface $activationDate): self
    {
        $this->activationDate = $activationDate;

        return $this;
    }

    public function getRegisterIp(): ?string
    {
        return $this->registerIp;
    }

    public function setRegisterIp(string $registerIp): self
    {
        $this->registerIp = $registerIp;

        return $this;
    }

    public function getRegisterDate(): ?\DateTimeInterface
    {
        return $this->registerDate;
    }

    public function setRegisterDate(\DateTimeInterface $registerDate): self
    {
        $this->registerDate = $registerDate;

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        if(in_array('ROLE_ADMIN', $this->roles)){
            $roles[] = 'ROLE_MODERATOR';
        }
        return array_unique($roles);
    }

    public function getActivationCode(): ?string
    {
        return $this->activationCode;
    }

    public function setActivationCode(?string $activationCode): self
    {
        $this->activationCode = $activationCode;

        return $this;
    }
}
