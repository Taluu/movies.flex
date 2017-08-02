<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Serializer\SerializerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use App\MovieNotFoundException;
use App\Repository\MoviesInterface as MoviesRepositoryInterface;

/** @Route("/v1/movies") */
class MoviesController
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var MoviesRepositoryInterface */
    private $repository;

    public function __construct(SerializerInterface $serializer, MoviesRepositoryInterface $repository)
    {
        $this->serializer = $serializer;
        $this->repository = $repository;
    }

    /**
     * @Route("")
     * @Method("GET")
     */
    public function getMovies(): Response
    {
        $movies = $this->repository->getAll();

        return new JsonResponse($this->serializer->serialize($movies, 'json'), 200, [], true);
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"})
     * @Method("GET")
     */
    public function getMovie($id): Response
    {
        try {
            $movie = $this->repository->get($id);

            return new JsonResponse($this->serializer->serialize($movie, 'json'), 200, [], true);
        } catch (MovieNotFoundException $e) {
            return new JsonResponse(['error' => "Movie {$id} not found"], 404);
        }
    }
}
