<?php

namespace App\Repository;

use App\Entity\Apprenticeship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Apprenticeship>
 *
 * @method Apprenticeship|null find($id, $lockMode = null, $lockVersion = null)
 * @method Apprenticeship|null findOneBy(array $criteria, array $orderBy = null)
 * @method Apprenticeship[]    findAll()
 * @method Apprenticeship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApprenticeshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Apprenticeship::class);
    }

    public function save(Apprenticeship $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Apprenticeship $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
}