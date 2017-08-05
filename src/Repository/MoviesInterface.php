<?php
namespace App\Repository;

use App\Entity\Movie;

interface MoviesInterface
{
    /** @throws MovieNotFoundException */
    public function get(string $hash): Movie;

    /** @return Movie[] */
    public function getAll(int $start = 0, ?int $limit = null, ?string $order = null, string $direction = 'asc', bool $showDeleted = false): iterable;

    /** Delete (soft or not) a Movie */
    public function delete(Movie $movie, bool $soft = true): void;
}
