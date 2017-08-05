<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    /** @var RequestStack */
    private $stack;

    public function __construct(RequestStack $stack, MoviesRepositoryInterface $repository)
    {
        $this->stack = $stack;
        $this->repository = $repository;
    }

    /**
     * @Route("")
     * @Method("GET")
     */
    public function getMovies(): iterable
    {
        // note
        // for a reason of HUGE coupling, we can't actually inject Request here
        // so we need the RequestStack as a dependency...
        //
        // yes, that's crappy.
        //
        // @see https://github.com/symfony/symfony/issues/23788
        // @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/issues/463
        $request = $this->stack->getCurrentRequest();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', null);

        $order = $request->query->get('order', null);
        $direction = $request->query->get('direction', 'asc');

        if (!in_array($order, [null, 'name'], true)) {
            throw new BadRequestHttpException("Expected \"name\" or no value for order, had \"{$order}\"");
        } elseif (!in_array($direction, ['asc', 'desc'])) {
            throw new BadRequestHttpException("Expected \"asc\" or \"desc\" for order direction, had \"{$direction}\"");
        }

        return $this->repository->getAll($start, $limit, $order, $direction);
    }

    /**
     * @Route("/{hash}", requirements={"hash": "^[0-9a-zA-Z]{40}$"})
     * @Method("GET")
     */
    public function getMovie($hash): Movie
    {
        return $this->repository->get($hash);
    }

    /**
     * @Route("/{hash}", requirements={"hash": "^[0-9a-zA-Z]{40}$"})
     * @Method("DELETE")
     */
    public function delete($hash): JsonResponse
    {
        $movie = $this->repository->get($hash);
        $this->repository->delete($movie);

        return new JsonResponse(null, 204);
    }
}
