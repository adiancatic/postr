<?php

namespace App\Repository;

use App\Entity\Relationship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Relationship|null find($id, $lockMode = null, $lockVersion = null)
 * @method Relationship|null findOneBy(array $criteria, array $orderBy = null)
 * @method Relationship[]    findAll()
 * @method Relationship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelationshipRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Relationship::class);
        $this->security = $security;
    }

    public function getFollowersOf($id)
    {
        return $relationships = $this->findBy([
            "followed" => $id,
            "active" => true,
        ]);
    }

    public function getFollowsOf($id)
    {
        return $this->findBy([
            "follower" => $id,
            "active" => true,
        ]);
    }

    public function isFollowed($id)
    {
        $relationships = $this->findBy([
            "follower" => $this->security->getUser()->getId(),
            "followed" => $id,
            "active" => true,
        ]);

        return count($relationships) > 0;
    }

    // /**
    //  * @return Relationship[] Returns an array of Relationship objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Relationship
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
