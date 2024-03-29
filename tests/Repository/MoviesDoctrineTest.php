<?php
namespace App\Repository;

use PHPUnit\Framework\TestCase;

use Prophecy\Argument;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\AbstractQuery as Query;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\Pagination\Paginator;

use App\Entity\Movie;

class MoviesDoctrineTest extends TestCase
{
    public function test_soft_delete()
    {
        $movie = new Movie('foo');
        $metadata = new ClassMetadata('movie');

        $prophecy = $this->prophesize(EntityManagerInterface::class);
        $prophecy->flush()->shouldBeCalled();
        $prophecy->persist($movie)->shouldBeCalled();
        $prophecy->remove($movie)->shouldNotBeCalled();
        $em = $prophecy->reveal();

        $repo = new MoviesDoctrine($em, $metadata);
        $repo->delete($movie, true);

        $this->assertTrue($movie->isDeleted());
    }

    public function test_hard_delete()
    {
        $movie = new Movie('foo');
        $metadata = new ClassMetadata('movie');

        $prophecy = $this->prophesize(EntityManagerInterface::class);
        $prophecy->flush()->shouldBeCalled();
        $prophecy->remove($movie)->shouldBeCalled();
        $prophecy->persist($movie)->shouldNotBeCalled();
        $em = $prophecy->reveal();

        $repo = new MoviesDoctrine($em, $metadata);
        $repo->delete($movie, false);
    }

    public function test_getMovie()
    {
        $movie = new Movie('foo');
        $metadata = new ClassMetadata('movie');

        $query = $this->prophesize(Query::class);
        $query->setParameter('hash', 'foo')->shouldBeCalled();
        $query->getSingleResult()->willReturn($movie)->shouldBeCalled();

        $qb = $this->prophesize(QueryBuilder::class);
        $qb->select('m')->willReturn($qb)->shouldBeCalled();
        $qb->from('movie', 'm', null)->willReturn($qb)->shouldBeCalled();
        $qb->where('sha1(m.id) = :hash')->shouldBeCalled();
        $qb->getQuery()->willReturn($query)->shouldBeCalled();

        $prophecy = $this->prophesize(EntityManagerInterface::class);
        $prophecy->createQueryBuilder()->willReturn($qb)->shouldBeCalled();
        $em = $prophecy->reveal();

        $repo = new MoviesDoctrine($em, $metadata);
        $repo->get('foo');
    }

    /**
     * @expectedException App\MovieNotFoundException
     * @expectedExceptionMessage Movie foo was not found
     */
    public function test_getMovie_not_found()
    {
        $metadata = new ClassMetadata('movie');

        $query = $this->prophesize(Query::class);
        $query->setParameter('hash', 'foo')->shouldBeCalled();
        $query->getSingleResult()->willThrow(new NoResultException)->shouldBeCalled();

        $qb = $this->prophesize(QueryBuilder::class);
        $qb->select('m')->willReturn($qb)->shouldBeCalled();
        $qb->from('movie', 'm', null)->willReturn($qb)->shouldBeCalled();
        $qb->where('sha1(m.id) = :hash')->shouldBeCalled();
        $qb->getQuery()->willReturn($query)->shouldBeCalled();

        $prophecy = $this->prophesize(EntityManagerInterface::class);
        $prophecy->createQueryBuilder()->willReturn($qb)->shouldBeCalled();
        $em = $prophecy->reveal();

        $repo = new MoviesDoctrine($em, $metadata);
        $repo->get('foo');
    }

    public function test_getAll_without_pagination_or_order_and_no_soft_deleted_records()
    {
        $movie = new Movie('foo');
        $metadata = new ClassMetadata('movie');

        $query = $this->prophesize(Query::class);
        $query->getResult()->willReturn([$movie])->shouldBeCalled();

        $qb = $this->prophesize(QueryBuilder::class);
        $qb->where('m.deleted = false')->shouldBeCalled();
        $qb->select('m')->willReturn($qb)->shouldBeCalled();
        $qb->getQuery()->willReturn($query)->shouldBeCalled();
        $qb->setMaxResults(Argument::any())->shouldNotBeCalled();
        $qb->setFirstResult(Argument::any())->shouldNotBeCalled();
        $qb->from('movie', 'm', null)->willReturn($qb)->shouldBeCalled();
        $qb->orderBy(Argument::any(), Argument::any())->shouldNotBeCalled();

        $prophecy = $this->prophesize(EntityManagerInterface::class);
        $prophecy->createQueryBuilder()->willReturn($qb)->shouldBeCalled();
        $em = $prophecy->reveal();

        $repo = new MoviesDoctrine($em, $metadata);
        $movies = $repo->getAll();

        $this->assertContainsOnlyInstancesOf(Movie::class, $movies);
    }

    public function test_getAll_without_pagination_or_order_and_soft_deleted_records()
    {
        $movie = new Movie('foo');
        $metadata = new ClassMetadata('movie');

        $query = $this->prophesize(Query::class);
        $query->getResult()->willReturn([$movie])->shouldBeCalled();

        $qb = $this->prophesize(QueryBuilder::class);
        $qb->select('m')->willReturn($qb)->shouldBeCalled();
        $qb->where('m.deleted = false')->shouldNotBeCalled();
        $qb->getQuery()->willReturn($query)->shouldBeCalled();
        $qb->from('movie', 'm', null)->willReturn($qb)->shouldBeCalled();
        $qb->setMaxResults(Argument::any())->shouldNotBeCalled();
        $qb->setFirstResult(Argument::any())->shouldNotBeCalled();
        $qb->orderBy(Argument::any(), Argument::any())->shouldNotBeCalled();

        $prophecy = $this->prophesize(EntityManagerInterface::class);
        $prophecy->createQueryBuilder()->willReturn($qb)->shouldBeCalled();
        $em = $prophecy->reveal();

        $repo = new MoviesDoctrine($em, $metadata);
        $movies = $repo->getAll(0, null, null, 'asc', true);

        $this->assertContainsOnlyInstancesOf(Movie::class, $movies);
    }

    public function test_getAll_with_order_but_no_pagination()
    {
        $movie = new Movie('foo');
        $metadata = new ClassMetadata('movie');

        $query = $this->prophesize(Query::class);
        $query->getResult()->willReturn([$movie])->shouldBeCalled();

        $qb = $this->prophesize(QueryBuilder::class);
        $qb->orderBy('m.foo', 'asc')->shouldBeCalled();
        $qb->where('m.deleted = false')->shouldBeCalled();
        $qb->select('m')->willReturn($qb)->shouldBeCalled();
        $qb->getQuery()->willReturn($query)->shouldBeCalled();
        $qb->setMaxResults(Argument::any())->shouldNotBeCalled();
        $qb->setFirstResult(Argument::any())->shouldNotBeCalled();
        $qb->from('movie', 'm', null)->willReturn($qb)->shouldBeCalled();

        $prophecy = $this->prophesize(EntityManagerInterface::class);
        $prophecy->createQueryBuilder()->willReturn($qb)->shouldBeCalled();
        $em = $prophecy->reveal();

        $repo = new MoviesDoctrine($em, $metadata);
        $movies = $repo->getAll(0, null, 'foo', 'asc');

        $this->assertContainsOnlyInstancesOf(Movie::class, $movies);
    }

    public function test_getAll_With_pagination_but_no_order()
    {
        $movie = new Movie('foo');
        $metadata = new ClassMetadata('movie');

        $query = $this->prophesize(Query::class);
        $query->getResult()->shouldNotBeCalled();

        $qb = $this->prophesize(QueryBuilder::class);
        $qb->where('m.deleted = false')->shouldBeCalled();
        $qb->orderBy('m.foo', 'asc')->shouldNotBeCalled();
        $qb->select('m')->willReturn($qb)->shouldBeCalled();
        $qb->setMaxResults(5)->willReturn($qb)->shouldBeCalled();
        $qb->setFirstResult(0)->willReturn($qb)->shouldBeCalled();
        $qb->from('movie', 'm', null)->willReturn($qb)->shouldBeCalled();
        $qb->getQuery()->willReturn($query)->shouldBeCalled(); // because Paginator

        $prophecy = $this->prophesize(EntityManagerInterface::class);
        $prophecy->createQueryBuilder()->willReturn($qb)->shouldBeCalled();
        $em = $prophecy->reveal();

        $repo = new MoviesDoctrine($em, $metadata);
        $movies = $repo->getAll(0, 5);

        $this->assertInstanceOf(Paginator::class, $movies);
    }

    public function test_getAll_with_pagination_and_order_should_not_trigger_pagination()
    {
        $movie = new Movie('foo');
        $metadata = new ClassMetadata('movie');

        $query = $this->prophesize(Query::class);
        $query->getResult()->willReturn([$movie])->shouldBeCalled();

        $qb = $this->prophesize(QueryBuilder::class);
        $qb->setMaxResults(5)->shouldNotBeCalled();
        $qb->setFirstResult(0)->shouldNotBeCalled();
        $qb->orderBy('m.foo', 'asc')->shouldBeCalled();
        $qb->where('m.deleted = false')->shouldBeCalled();
        $qb->select('m')->willReturn($qb)->shouldBeCalled();
        $qb->getQuery()->willReturn($query)->shouldBeCalled();
        $qb->from('movie', 'm', null)->willReturn($qb)->shouldBeCalled();

        $prophecy = $this->prophesize(EntityManagerInterface::class);
        $prophecy->createQueryBuilder()->willReturn($qb)->shouldBeCalled();
        $em = $prophecy->reveal();

        $repo = new MoviesDoctrine($em, $metadata);
        $movies = $repo->getAll(0, 5, 'foo', 'asc');

        $this->assertContainsOnlyInstancesOf(Movie::class, $movies);
    }
}
