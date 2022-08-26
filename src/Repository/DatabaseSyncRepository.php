<?php

namespace App\Repository;

use App\Entity\DatabaseSync;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DatabaseSync>
 *
 * @method DatabaseSync|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatabaseSync|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatabaseSync[]    findAll()
 * @method DatabaseSync[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatabaseSyncRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DatabaseSync::class);
    }

    public function add(DatabaseSync $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DatabaseSync $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return DatabaseSync[] Returns an array of DatabaseSync objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

}
