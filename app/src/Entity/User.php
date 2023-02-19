<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int    $id       = null;
    
    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;
    
    #[ORM\Column]
    private array   $roles    = [];
    
    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password  = null;
    
    #[ORM\Column(length: 255)]
    private ?string $firstname = null;
    
    #[ORM\Column(length: 255)]
    private ?string $lastname  = null;

    #[ORM\OneToMany(mappedBy: 'azubiId', targetEntity: Apprenticeship::class)]
    private Collection $apprenticeships;

    #[ORM\OneToMany(mappedBy: 'ausbilderId', targetEntity: Apprenticeship::class)]
    private Collection $courses;

    public function __construct()
    {
        $this->apprenticeships = new ArrayCollection();
        $this->courses = new ArrayCollection();
    }
    
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
    
    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }
    
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        
        return array_unique($roles);
    }
    
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        
        return $this;
    }
    
    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }
    
    public function setPassword(string $password): self
    {
        $this->password = $password;
        
        return $this;
    }
    
    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
        $this->password = '';
    }
    
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }
    
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        
        return $this;
    }
    
    public function getLastname(): ?string
    {
        return $this->lastname;
    }
    
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        
        return $this;
    }

    /**
     * @return Collection<int, Apprenticeship>
     */
    public function getApprenticeships(): Collection
    {
        return $this->apprenticeships;
    }

    public function addApprenticeship(Apprenticeship $apprenticeship): self
    {
        if (!$this->apprenticeships->contains($apprenticeship)) {
            $this->apprenticeships->add($apprenticeship);
            $apprenticeship->setAzubiId($this);
        }

        return $this;
    }

    public function removeApprenticeship(Apprenticeship $apprenticeship): self
    {
        if ($this->apprenticeships->removeElement($apprenticeship)) {
            // set the owning side to null (unless already changed)
            if ($apprenticeship->getAzubiId() === $this) {
                $apprenticeship->setAzubiId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Apprenticeship>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Apprenticeship $course): self
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
            $course->setAusbilderId($this);
        }

        return $this;
    }

    public function removeCourse(Apprenticeship $course): self
    {
        if ($this->courses->removeElement($course)) {
            // set the owning side to null (unless already changed)
            if ($course->getAusbilderId() === $this) {
                $course->setAusbilderId(null);
            }
        }

        return $this;
    }
    
}
