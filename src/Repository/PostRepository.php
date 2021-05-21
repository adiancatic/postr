<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    protected $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Post::class);

        $this->security = $security;
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
                JOIN App\Entity\User u
                WHERE p.user_id = :id AND p.user_id = u.id
                ORDER BY p.created_at DESC
            ")
            ->setParameter("id", $id)
            ->getResult();
    }

    public function getPostsFromFollowedUsers($id)
    {
        $followedUsers = $this->getEntityManager()
            ->createQuery("
                SELECT IDENTITY(r.followed)
                FROM App\Entity\Relationship r
                WHERE r.follower = :id AND r.active = 1
            ")
            ->getDQL();

        return $this->getEntityManager()
            ->createQuery("
                SELECT p.id, p.content, p.created_at, u.id as user, u.username
                FROM App\Entity\Post p
                JOIN App\Entity\User u
                WHERE u.id = p.user_id AND (p.user_id = :current_user OR p.user_id IN (" . $followedUsers . "))
                ORDER BY p.created_at DESC
            ")
            ->setParameters(["id" => $id, "current_user" => $this->security->getUser()->getId()])
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
