<?php

namespace App\Repository;

use App\Entity\MAPlocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MAPlocation>
 *
 * @method MAPlocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MAPlocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MAPlocation[]    findAll()
 * @method MAPlocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MAPlocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MAPlocation::class);
    }

    public function add(MAPlocation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MAPlocation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
