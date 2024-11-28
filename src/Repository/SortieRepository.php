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

    public function findByFilters(array $filters, ?User $user): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.campusOrganisateur', 'c')
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.etat', 'e')
            ->addSelect('c', 'p', 'e');

        if (!empty($filters['campusOrganisateur'])) {
            $qb->andWhere('c.id = :campus')
                ->setParameter('campus', $filters['campusOrganisateur']->getId());
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('s.name LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['date_start'])) {
            $qb->andWhere('s.dateStart >= :date_start')
                ->setParameter('date_start', $filters['date_start']);
        }

        if (!empty($filters['date_end'])) {
            $qb->andWhere('s.dateLimitInscription <= :date_end')
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
            $qb->andWhere('e.libelle = :etatPasse')
                ->setParameter('etatPasse', 'Passée');
        }

        $oneMonthAgo = new \DateTimeImmutable('-30 days');
        $qb->andWhere('s.dateStart >= :oneMonthAgo')
            ->setParameter('oneMonthAgo', $oneMonthAgo);

        return $qb->orderBy('s.dateStart', 'ASC')->getQuery();
    }
}
