<?php

namespace App\Repository;

use App\Entity\MAPmap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MAPmap>
 *
 * @method MAPmap|null find($id, $lockMode = null, $lockVersion = null)
 * @method MAPmap|null findOneBy(array $criteria, array $orderBy = null)
 * @method MAPmap[]    findAll()
 * @method MAPmap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MAPmapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MAPmap::class);
    }

    public function add(MAPmap $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush)
        {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MAPmap $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush)
        {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * findMaps - Get public (and user if connected) maps list
     *
     * @param Integer $userId
     *
     * @return Array maps list query result
     */
    public function findMaps(
        int $userId
    ): array
    {
        // the "m" is an alias used in the query
        $qb = $this->createQueryBuilder('m')
            ->where("m.private = 0 and m.password = ''");

        if ($userId > 0)
        {
            $qb->orWhere('m.user = :user')
                ->setParameter('user', $userId);
        }
        $query = $qb->getQuery();
        return $query->execute();
    }

    /**
     * findMapsByName - Get public (and user if connected) maps search by name list
     *
     * @param Integer $userId
     * @param String $mapName
     *
     * @return Array maps list query result
     */
    public function findMapsByName(
        int $userId = 0,
        string $mapName = ''
    ): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where("LOWER(m.name) LIKE :name AND m.private = 0 AND m.password = ''")
            ->setParameter('name', '%'.strtolower($mapName).'%');

        if ($userId > 0)
        {
            $qb->orWhere("LOWER(m.name) LIKE :name AND m.user = :user")
                ->setParameter('name', '%'.strtolower($mapName).'%')
                ->setParameter('user', $userId);
        }
        $query = $qb->getQuery();
        return $query->execute();
    }
}
