<?php

namespace Fm\CronjobManagerBundle\Repository;

use Fm\CronjobManagerBundle\Entity\FmCronjobManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FmCronjobManager>
 *
 * @method FmCronjobManager|null find($id, $lockMode = null, $lockVersion = null)
 * @method FmCronjobManager|null findOneBy(array $criteria, array $orderBy = null)
 * @method FmCronjobManager[]    findAll()
 * @method FmCronjobManager[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FmCronjobManagerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FmCronjobManager::class);
    }

    /**
     * @return FmCronjobManager[]
     */
    public function findToArray(array $criteria = null): array
    {
        $qb = $this
            ->createQueryBuilder('c');

        if ($criteria != null) {
            $where_criteria = $criteria['where'];
            foreach ($where_criteria as $where_array) {
                $qb->where("c." . $where_array[0] . " = :$where_array[0]")
                   ->setParameter("$where_array[0]", "$where_array[1]");
            }
        }

        return $qb
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param FmCronjobManager $entity
     * @param bool $flush
     *
     * @return void
     */
    public function save(FmCronjobManager $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param FmCronjobManager $entity
     * @param bool $flush
     *
     * @return void
     */
    public function remove(FmCronjobManager $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Cronjobs[] Returns an array of Cronjobs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Cronjobs
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
