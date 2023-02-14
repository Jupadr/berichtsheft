<?php

namespace App\Entity;

use App\Repository\ApprenticeshipRepository;
use App\Repository\ApprenticeshopRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV4;

#[ORM\Entity(repositoryClass: ApprenticeshipRepository::class)]
class Apprenticeship
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(type: 'string')]
    private string $inviteLink;
    
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

    public function getInviteLink(): string
    {
        return $this->inviteLink;
    }

    public function setInviteLink(string $inviteLink)
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