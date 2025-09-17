<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if ( ! $user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function countUsersByMonth2025(): array
    {
        // Récupérer tous les utilisateurs inscrits en 2025
        $users = $this->createQueryBuilder('u')
            ->andWhere('u.createdAt BETWEEN :start AND :end')
            ->setParameter('start', new \DateTimeImmutable('2025-01-01 00:00:00'))
            ->setParameter('end', new \DateTimeImmutable('2025-12-31 23:59:59'))
            ->getQuery()
            ->getResult(); // On récupère des objets User

        // Initialiser tous les mois à 0
        $counts = [];
        for ($m = 1; $m <= 12; $m++) {
            $month = sprintf('2025-%02d', $m);
            $counts[$month] = 0;
        }

        // Compter les utilisateurs par mois
        foreach ($users as $user) {
            $month = $user->getCreatedAt()->format('Y-m');
            $counts[$month]++;
        }

        // Transformer en tableau pour le chart
        $result = [];
        foreach ($counts as $month => $count) {
            $result[] = ['month' => $month, 'count' => $count];
        }

        return $result;
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
