<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityNotFoundException;

use App\Entity\Movie;

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
        } catch (EntityNotFoundException $e) {
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
