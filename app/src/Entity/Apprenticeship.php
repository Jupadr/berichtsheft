<?php

namespace App\Entity;

use App\Repository\ApprenticeshipRepository;
use Cassandra\Date;
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

    #[ORM\Column(type: 'string')]
    private string $inviteToken;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $azubiId;

    #[ORM\Column(type: 'integer')]
    private ?int $instructorId;

    #[ORM\Column(type: 'date')]
    private ?Date  $startApprenticeship;

    #[ORM\Column(type: 'date')]
    private ?Date $endApprenticeship;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
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
     * @param string $inviteToken
     */
    public function setInviteToken(string $inviteToken): void
    {
        $this->inviteToken = $inviteToken;
    }

    /**
     * @return string|null
     */
    public function getAzubiId(): ?string
    {
        return $this->azubiId;
    }

    /**
     * @param string|null $azubiId
     */
    public function setAzubiId(?string $azubiId): void
    {
        $this->azubiId = $azubiId;
    }

    /**
     * @return int|null
     */
    public function getInstructorId(): ?int
    {
        return $this->instructorId;
    }

    /**
     * @param int|null $instructorId
     */
    public function setInstructorId(?int $instructorId): void
    {
        $this->instructorId = $instructorId;
    }

    /**
     * @return Date|null
     */
    public function getStartApprenticeship(): ?Date
    {
        return $this->startApprenticeship;
    }

    /**
     * @param Date|null $startApprenticeship
     */
    public function setStartApprenticeship(?Date $startApprenticeship): void
    {
        $this->startApprenticeship = $startApprenticeship;
    }

    /**
     * @return Date|null
     */
    public function getEndApprenticeship(): ?Date
    {
        return $this->endApprenticeship;
    }

    /**
     * @param Date|null $endApprenticeship
     */
    public function setEndApprenticeship(?Date $endApprenticeship): void
    {
        $this->endApprenticeship = $endApprenticeship;
    }
}