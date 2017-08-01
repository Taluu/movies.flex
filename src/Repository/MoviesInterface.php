<?php
namespace App\Repository;

use App\Entity\Movie;

interface MoviesInterface
{
    /** @throws MovieNotFoundException */
    public function get(int $id): Movie;

    /** @return Movie[] */
    public function getAll(): iterable;
}
