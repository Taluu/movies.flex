<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

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
    public function getAll(): iterable
    {
        $builder = $this->createQueryBuilder('m');
        $query = $builder->getQuery();

        yield from $query->getResult();
    }
}
