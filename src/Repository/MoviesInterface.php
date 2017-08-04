<?php
namespace App\Repository;

use App\Entity\Movie;

interface MoviesInterface
{
    /** @throws MovieNotFoundException */
    public function get(string $hash): Movie;

    /** @yield Movie */
    public function getAll(int $start = 0, ?int $limit = 5): iterable;
}
