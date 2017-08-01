<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;

use App\Entity\Movie;

class Movies extends EntityRepository implements MoviesInterface
{
    /** {@inheritDoc} */
    public function get(int $id): Movie
    {
        throw new \BadMethodCall('Not implemented yet');
    }

    /** {@inheritDoc} */
    public function getAll(): iterable
    {
        throw new \BadMethodCall('Not implemented yet');
    }
}
