<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

use App\Entity\Movie;
use App\MovieNotFoundException;

class MoviesDoctrine extends EntityRepository implements MoviesInterface
{
    /** {@inheritDoc} */
    public function get(string $hash): Movie
    {
        $builder = $this->createQueryBuilder('m');
        $builder->where('sha1(m.id) = :hash');

        $query = $builder->getQuery();
        $query->setParameter('hash', $hash);

        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            throw new MovieNotFoundException($hash, $e);
        }
    }

    /** {@inheritDoc} */
    public function getAll(int $start = 0, ?int $limit = 5, ?string $order = 'id', string $direction = 'asc'): iterable
    {
        $builder = $this->createQueryBuilder('m');

        if (null !== $order) {
            $builder->orderBy("m.{$order}", $direction);
        }

        $query = $builder->getQuery();

        // can't use order AND limit until doctrine 2.6 with Paginator with MySQL 5.7
        // @see https://github.com/doctrine/doctrine2/pull/6143
        // @see https://github.com/doctrine/doctrine2/issues/5622
        if (null === $order && null !== $limit) {
            $query
                ->setFirstResult($start)
                ->setMaxResults($limit)
            ;

            return new Paginator($query);
        }

        return $query->getResult();
    }
}
