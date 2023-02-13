<?php

namespace App\Entity;

use App\Repository\ApprenticeshipRepository;
use App\Repository\ApprenticeshopRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
#[ORM\Entity(repositoryClass: ApprenticeshipRepository::class)]
class Apprenticeship
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column()]
    private ?UuidType $inviteLink;
    
    #[ORM\Column(length: 180, unique: true)]
    private ?string $AzubiName;

    #[ORM\Column(type: 'datetime')]
    private ?DateTime  $startApprenticeship;

    #[ORM\Column(type: 'datetime')]
    private ?DateTime $endApprenticeship;

    public function getId(): int
    {
        return $this->id;
    }

    public function getInviteLink(): UuidType
    {
        return $this->inviteLink;
    }

    public function setInviteLink(UuidType $inviteLink)
    {
        $this->inviteLink = $inviteLink;
    }

    public function getName(): string
    {
        return $this->AzubiName;
    }

    public function setName(string $name)
    {
        $this->AzubiName = $name;
    }

    public function getStartApprenticeship(): DateTime
    {
        return $this->startApprenticeship;
    }

    public function setStartApprenticeship(datetime $startApprenticeship)
    {
        $this->startApprenticeship =  $startApprenticeship;
    }

    public function getEndApprenticeship(): DateTime
    {
        return $this->startApprenticeship;
    }

    public function setEndApprenticeship(DateTime $endApprenticeship)
    {
        $this->endApprenticeship = $endApprenticeship;
    }


    

}