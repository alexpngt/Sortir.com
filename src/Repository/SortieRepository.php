<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByFilters(array $filters, ?User $user)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.campusOrganisateur', 'c')
            ->leftJoin('s.participants', 'p')
            ->addSelect('c', 'p');

        if (!empty($filters['campus'])) {
            $qb->andWhere('c.id = :campus')
                ->setParameter('campus', $filters['campus']->getId());
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['date_start'])) {
            $qb->andWhere('s.dateHeureDebut >= :date_start')
                ->setParameter('date_start', $filters['date_start']);
        }

        if (!empty($filters['date_end'])) {
            $qb->andWhere('s.dateHeureDebut <= :date_end')
                ->setParameter('date_end', $filters['date_end']);
        }

        if (!empty($filters['organisateur']) && $user) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        if (!empty($filters['inscrit']) && $user) {
            $qb->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        if (!empty($filters['non_inscrit']) && $user) {
            $qb->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        if (!empty($filters['passees'])) {
            $qb->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
