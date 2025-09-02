<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function searchProduct(array $criteria)
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.category', 'c');
        if ( ! empty($criteria['q'])) {
            $qb->andWhere('MATCH_AGAINST(p.title, p.description) AGAINST (:q boolean) > 0')
                ->setParameter('q', $criteria['q']);
        }
        if ( ! empty($criteria['c'])) {
            $qb->andWhere('c.slug IN (:cats)')
                ->setParameter('cats', $criteria['c']);
        }

        return $qb->orderBy('p.createdAt', 'DESC')->getQuery()->getResult();
    }

}
