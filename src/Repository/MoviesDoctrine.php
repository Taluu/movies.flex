<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

use App\Entity\Movie;
use App\MovieNotFoundException;

class MoviesDoctrine extends EntityRepository implements MoviesInterface
{
    /** {@inheritDoc} */
    public function get(int $id): Movie
    {
        $builder = $this->createQueryBuilder('m');
        $builder->where('m.id = :id');

        $query = $builder->getQuery();
        $query->setParameter('id', $id);

        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            throw new MovieNotFoundException($id, $e);
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
