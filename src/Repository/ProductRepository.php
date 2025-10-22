<?php

namespace App\Repository;

use App\Entity\Product;
use App\Model\SearchData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{

    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $paginator,
        private readonly RequestStack $request
    ) {
        parent::__construct($registry, Product::class);
    }

    public function latest(): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('i', 'c', 'b')
            ->leftJoin('p.images', 'i')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.brand', 'b')
            ->orderBy('p.createdAt', 'DESC')
            ->groupBy('c.id')
            ->setMaxResults(4)
            ->getQuery()->getResult();
    }

    public function searchProduct(SearchData $search): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('i', 'b', 'c')
            ->leftJoin('p.images', 'i')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.brand', 'b')
            ->orderBy('p.createdAt', 'DESC');
        if ( ! empty($search->q)) {
            $qb->andWhere('MATCH_AGAINST(p.title, p.description) AGAINST(:query BOOLEAN) > 0')
                ->setParameter('query', $search->q);
        }
        if ( ! empty($search->c)) {
            $qb->andWhere('c = :cat')
                ->setParameter('cat', $search->c);
        }
        if ( ! empty($search->b)) {
            $qb->andWhere('b IN (:brands)')
                ->setParameter('brands', $search->b);
        }

        $query = $qb->getQuery();

        return $this->paginator->paginate($query, $this->request->getCurrentRequest()->query->getInt('page', 1), 20);
    }
}
