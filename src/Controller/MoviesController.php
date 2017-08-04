<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Serializer\SerializerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use App\Entity\Movie;
use App\Repository\MoviesInterface as MoviesRepositoryInterface;

use App\MovieNotFoundException;

/** @Route("/v1/movies") */
class MoviesController
{
    /** @var MoviesRepositoryInterface */
    private $repository;

    public function __construct(MoviesRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route("")
     * @Method("GET")
     */
    public function getMovies(): iterable
    {
        return iterator_to_array($this->repository->getAll());
    }

    /**
     * @Route("/{hash}", requirements={"hash": "^[0-9a-zA-Z]{40}$"})
     * @Method("GET")
     */
    public function getMovie($hash): Movie
    {
        return $this->repository->get($hash);
    }
}
