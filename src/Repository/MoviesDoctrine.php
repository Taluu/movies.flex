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
    public function getAll(int $start = 0, ?int $limit = null, ?string $order = null, string $direction = 'asc', bool $showDeleted = false): iterable
    {
        $builder = $this->createQueryBuilder('m');

        if (null !== $order) {
            $builder->orderBy("m.{$order}", $direction);
        }

        if (false === $showDeleted) {
            $builder->where('m.deleted = false');
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

    /** {@inheritDoc} */
    public function delete(Movie $movie, bool $soft = true): void
    {
        $em = $this->getEntityManager();

        if (!$soft) {
            $em->remove($movie);
            $em->flush();

            return;
        }

        $movie->delete();

        $em->persist($movie);
        $em->flush();
    }
}
