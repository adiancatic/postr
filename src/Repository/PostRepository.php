<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return mixed
     */
    public function findAllWithAuthors()
    {
        return $this->getEntityManager()
            ->createQuery("
                    SELECT p.id, p.content, p.created_at, u.id as user, u.username
                    FROM App\Entity\Post p
                    JOIN App\Entity\User u WHERE p.user_id=u.id
                    ORDER BY p.created_at DESC
            ")
            ->getResult();
    }

    public function findByIdWithAuthors($id)
    {
        return $this->getEntityManager()
            ->createQuery("
                    SELECT p.id, p.content, p.created_at, u.id as user, u.username
                    FROM App\Entity\Post p
                    JOIN App\Entity\User u WHERE p.user_id = :uid AND p.user_id=u.id
                    ORDER BY p.created_at DESC
            ")
            ->setParameter("uid", $id)
            ->getResult();
    }


    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
