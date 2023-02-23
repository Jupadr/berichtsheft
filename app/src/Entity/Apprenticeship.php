<?php

namespace App\Entity;

use App\Repository\ApprenticeshipRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApprenticeshipRepository::class)]
class Apprenticeship
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int      $id;
    
    #[ORM\Column(name: 'invite_token', length: 255, unique: true)]
    private ?string   $inviteToken;
    
    #[ORM\Column(type: 'date')]
    private ?DateTime $startApprenticeship;
    
    #[ORM\Column(type: 'date')]
    private ?DateTime $endApprenticeship;
    
    #[ORM\Column(name: 'azubi_id', nullable: true)]
    #[ORM\ManyToOne(inversedBy: 'apprenticeships')]
    private ?int      $azubiId     = null;
    
    #[ORM\Column(name: 'ausbilder_id')]
    #[ORM\ManyToOne(inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?int      $ausbilderId = null;
    
    #[ORM\Column(length: 512)]
    private ?string   $title       = null;
    
    #[ORM\Column(name: 'company_name', length: 512)]
    private ?string   $companyName = null;

    #[ORM\OneToMany(mappedBy: 'apprenticeshipId', targetEntity: Entry::class)]
    private Collection $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }
    
    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * @param  int|null  $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    
    /**
     * @return string
     */
    public function getInviteToken(): string
    {
        return $this->inviteToken;
    }
    
    /**
     * @param  string  $inviteToken
     */
    public function setInviteToken(string $inviteToken): void
    {
        $this->inviteToken = $inviteToken;
    }
    
    /**
     * @return DateTime|null
     */
    public function getStartApprenticeship(): ?DateTime
    {
        return $this->startApprenticeship;
    }
    
    /**
     * @param  DateTime|null  $startApprenticeship
     */
    public function setStartApprenticeship(?DateTime $startApprenticeship): void
    {
        $this->startApprenticeship = $startApprenticeship;
    }
    
    /**
     * @return DateTime|null
     */
    public function getEndApprenticeship(): ?DateTime
    {
        return $this->endApprenticeship;
    }
    
    /**
     * @param  DateTime|null  $endApprenticeship
     */
    public function setEndApprenticeship(?DateTime $endApprenticeship): void
    {
        $this->endApprenticeship = $endApprenticeship;
    }
    
    public function getAzubiId(): ?int
    {
        return $this->azubiId;
    }
    
    public function setAzubiId(?int $azubiId): self
    {
        $this->azubiId = $azubiId;
        
        return $this;
    }
    
    public function getAusbilderId(): ?int
    {
        return $this->ausbilderId;
    }
    
    public function setAusbilderId(?int $ausbilderId): self
    {
        $this->ausbilderId = $ausbilderId;
        
        return $this;
    }
    
    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setTitle(string $title): self
    {
        $this->title = $title;
        
        return $this;
    }
    
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }
    
    public function setCompanyName(string $companyName): self
    {
        $this->companyName = $companyName;
        
        return $this;
    }

    /**
     * @return Collection<int, Entry>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(Entry $entry): self
    {
        if (!$this->entries->contains($entry)) {
            $this->entries->add($entry);
            $entry->setApprenticeshipId($this);
        }

        return $this;
    }

    public function removeEntry(Entry $entry): self
    {
        if ($this->entries->removeElement($entry)) {
            // set the owning side to null (unless already changed)
            if ($entry->getApprenticeshipId() === $this) {
                $entry->setApprenticeshipId(null);
            }
        }

        return $this;
    }
    
}