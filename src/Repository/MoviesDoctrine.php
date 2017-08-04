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
    public function getAll(int $start = 0, ?int $limit = 5): iterable
    {
        $builder = $this->createQueryBuilder('m');

        $query = $builder->getQuery();

        if (null !== $limit) {
            $query
                ->setFirstResult($start)
                ->setMaxResults($limit)
            ;

            return new Paginator($query);
        }

        return $query->getResult();
    }
}
