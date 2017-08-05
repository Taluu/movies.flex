<?php
namespace App\Controller;

use PHPUnit\Framework\TestCase;

use Prophecy\Argument;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

use Doctrine\ORM\Tools\Pagination\Paginator;

use App\Entity\Movie;
use App\MovieNotFoundException;
use App\Repository\MoviesInterface as MoviesRepositoryInterface;

class MoviesControllerTest extends TestCase
{
    public function testGetMovie()
    {
        $movie = new Movie('foo');
        $stack = new RequestStack;

        $prophecy = $this->prophesize(MoviesRepositoryInterface::class);
        $prophecy->get('foo')->willReturn($movie)->shouldBeCalled();
        $repository = $prophecy->reveal();

        $controller = new MoviesController($stack, $repository);
        $result = $controller->getMovie('foo');

        $this->assertInstanceOf(Movie::class, $result);
        $this->assertSame($movie, $result);
    }

    /**
     * @expectedException App\MovieNotFoundException
     * @expectedExceptionMessage Movie foo was not found
     */
    public function testGetMovieNotFound()
    {
        $stack = new RequestStack;

        $prophecy = $this->prophesize(MoviesRepositoryInterface::class);
        $prophecy->get('foo')->willThrow(new MovieNotFoundException('foo'))->shouldBeCalled();
        $repository = $prophecy->reveal();

        $controller = new MoviesController($stack, $repository);
        $controller->getMovie('foo');
    }

    public function testDelete()
    {
        $movie = new Movie('foo');
        $stack = new RequestStack;

        $prophecy = $this->prophesize(MoviesRepositoryInterface::class);
        $prophecy->get('foo')->willReturn($movie)->shouldBeCalled();
        $prophecy->delete($movie)->shouldBeCalled();
        $repository = $prophecy->reveal();

        $controller = new MoviesController($stack, $repository);
        $result = $controller->delete('foo');

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertSame(204, $result->getStatusCode());
    }

    /**
     * @expectedException App\MovieNotFoundException
     * @expectedExceptionMessage Movie foo was not found
     */
    public function testDeleteNotFound()
    {
        $stack = new RequestStack;

        $prophecy = $this->prophesize(MoviesRepositoryInterface::class);
        $prophecy->get('foo')->willThrow(new MovieNotFoundException('foo'))->shouldBeCalled();
        $prophecy->delete(Argument::type(Movie::class))->shouldNotBeCalled();
        $repository = $prophecy->reveal();

        $controller = new MoviesController($stack, $repository);
        $controller->delete('foo');
    }

    public function testGetAllWithoutOrderOrPagination()
    {
        $movie = new Movie('foo');

        $request = new Request;
        $stack = new RequestStack;

        $stack->push($request);

        $prophecy = $this->prophesize(MoviesRepositoryInterface::class);
        $prophecy->getAll(0, null, null, 'asc')->willReturn([$movie])->shouldBeCalled();
        $repository = $prophecy->reveal();

        $controller = new MoviesController($stack, $repository);
        $result = $controller->getMovies();

        $this->assertContainsOnlyInstancesOf(Movie::class, $result);
        $this->assertCount(1, $result);
    }

    /** @dataProvider directionProvider */
    public function testGetAllWithOrderButNoPagination($direction)
    {
        $movie = new Movie('foo');

        $request = new Request;
        $stack = new RequestStack;

        $request->query->set('order', 'name');
        $request->query->set('direction', $direction);

        $stack->push($request);

        $prophecy = $this->prophesize(MoviesRepositoryInterface::class);
        $prophecy->getAll(0, null, 'name', $direction)->willReturn([$movie])->shouldBeCalled();
        $repository = $prophecy->reveal();

        $controller = new MoviesController($stack, $repository);
        $result = $controller->getMovies();

        $this->assertContainsOnlyInstancesOf(Movie::class, $result);
        $this->assertCount(1, $result);
    }

    /**
     * @dataProvider directionProvider
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Expected "name" or no value for order, had "foo"
     */
    public function testGetAllWithWrongOrderAndNoPagination($direction)
    {
        $movie = new Movie('foo');

        $request = new Request;
        $stack = new RequestStack;

        $request->query->set('order', 'foo');
        $request->query->set('direction', $direction);

        $stack->push($request);

        $prophecy = $this->prophesize(MoviesRepositoryInterface::class);
        $prophecy->getAll(0, null, 'foo', $direction)->shouldNotBeCalled();
        $repository = $prophecy->reveal();

        $controller = new MoviesController($stack, $repository);
        $controller->getMovies();
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Expected "asc" or "desc" for order direction, had "foo"
     */
    public function testGetAllWithWrongOrderWrongDirectionAndNoPagination()
    {
        $movie = new Movie('foo');

        $request = new Request;
        $stack = new RequestStack;

        $request->query->set('order', 'name');
        $request->query->set('direction', 'foo');

        $stack->push($request);

        $prophecy = $this->prophesize(MoviesRepositoryInterface::class);
        $prophecy->getAll(0, null, 'name', 'foo')->shouldNotBeCalled();
        $repository = $prophecy->reveal();

        $controller = new MoviesController($stack, $repository);
        $controller->getMovies();
    }

    public function testGetAllWithPagination()
    {
        $movie = new Movie('foo');
        $paginator = $this->prophesize(Paginator::class)->reveal();

        $request = new Request;
        $request->query->set('limit', 2);

        $stack = new RequestStack;
        $stack->push($request);

        $prophecy = $this->prophesize(MoviesRepositoryInterface::class);
        $prophecy->getAll(0, 2, null, 'asc')->willReturn($paginator)->shouldBeCalled();
        $repository = $prophecy->reveal();

        $controller = new MoviesController($stack, $repository);
        $result = $controller->getMovies();

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function directionProvider(): array
    {
        return [
            'asc' => ['asc'],
            'desc' => ['desc']
        ];
    }
}
