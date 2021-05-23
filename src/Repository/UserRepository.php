<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    protected $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, User::class);

        $this->security = $security;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function getFollowedUsers()
    {
        if(!$this->security->getUser()) {
            return;
        }
        $followedUsers = $this->getEntityManager()
            ->createQuery("
                    SELECT IDENTITY(r.followed)
                    FROM App\Entity\Relationship r
                    WHERE r.follower = :current_user AND r.active = 1
            ")
            ->getDQL();

        return $this->getEntityManager()
            ->createQuery("
                    SELECT u.id, u.username
                    FROM App\Entity\User u
                    WHERE u.id IN (" . $followedUsers . ")
            ")
            ->setParameters(["current_user" => $this->security->getUser()->getId()])
            ->getResult();
    }

    public function getUsersYouDontFollow()
    {
        if(!$this->security->getUser()) {
            return;
        }
        $followedUsers = $this->getEntityManager()
            ->createQuery("
                    SELECT IDENTITY(r.followed)
                    FROM App\Entity\Relationship r
                    WHERE r.follower = :current_user AND r.active = 1
            ")
            ->getDQL();

        return $this->getEntityManager()
            ->createQuery("
                    SELECT u.id, u.username
                    FROM App\Entity\User u
                    WHERE u.id != :current_user OR u.id NOT IN (" . $followedUsers . ")
            ")
            ->setParameters(["current_user" => $this->security->getUser()->getId()])
            ->getResult();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
