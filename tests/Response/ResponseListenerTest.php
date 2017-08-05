<?php
namespace App\Response;

use RuntimeException;

use PHPUnit\Framework\TestCase;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

use App\MovieNotFoundException;

class ResponseListenerTest extends TestCase
{
    public function test_onKernelView_with_a_collection()
    {
        $data = [['id' => 'foo'], ['id' => 'bar']];

        $request = new Request;
        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();

        $normalizer = $this->prophesize(NormalizerInterface::class);
        $normalizer->normalize($data, null, ['groups' => ['public']])->willReturn($data);

        $event = new GetResponseForControllerResultEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $data);

        $listener = new ResponseListener($normalizer->reveal());
        $listener->onKernelView($event);

        $expect = [
            'count' => 2,
            'total' => 2,

            'data' => [
                [
                    'id' => sha1('foo')
                ],

                [
                    'id' => sha1('bar')
                ]
            ]
        ];

        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $event->getResponse()->getContent());
    }

    public function test_onKernelView_on_single_element()
    {
        $data = 'foo';
        $transformedData = [['id' => 'foo']];

        $request = new Request;
        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();

        $normalizer = $this->prophesize(NormalizerInterface::class);
        $normalizer->normalize([$data], null, ['groups' => ['public']])->willReturn($transformedData);

        $event = new GetResponseForControllerResultEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $data);

        $listener = new ResponseListener($normalizer->reveal());
        $listener->onKernelView($event);

        $expect = [
            'count' => 1,
            'total' => 1,

            'data' => [
                'id' => sha1('foo')
            ]
        ];

        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $event->getResponse()->getContent());
    }

    public function test_onKernelException_with_a_HttpExceptionInterface()
    {
        $request = new Request;
        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();
        $normalizer = $this->prophesize(NormalizerInterface::class)->reveal();

        $exception = new class extends RuntimeException implements HttpExceptionInterface {
            public function __construct()
            {
                parent::__construct('oops');
            }

            public function getStatusCode()
            {
                return 500;
            }

            public function getHeaders()
            {
                return [];
            }
        };

        $event = new GetResponseForExceptionEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception);

        $listener = new ResponseListener($normalizer);
        $listener->onKernelException($event);

        $expect = [
            'error' => 'oops',
            'http' => 500
        ];

        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $event->getResponse()->getContent());
    }

    public function test_onKernelException_with_a_MovieNotFoundException()
    {
        $request = new Request;
        $exception = new MovieNotFoundException('foo');
        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();
        $normalizer = $this->prophesize(NormalizerInterface::class)->reveal();

        $event = new GetResponseForExceptionEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception);

        $listener = new ResponseListener($normalizer);
        $listener->onKernelException($event);

        $expect = [
            'error' => 'Movie foo was not found',
            'http' => 404
        ];

        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $event->getResponse()->getContent());
    }

    public function test_onKernelException_with_an_unrelated_exception()
    {
        $request = new Request;
        $exception = new \Exception;
        $kernel = $this->prophesize(HttpKernelInterface::class)->reveal();
        $normalizer = $this->prophesize(NormalizerInterface::class)->reveal();

        $event = new GetResponseForExceptionEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception);

        $listener = new ResponseListener($normalizer);
        $listener->onKernelException($event);

        $expect = [
            'error' => 'Oops, something nasty happened !',
            'http' => 500
        ];

        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $event->getResponse()->getContent());
    }
}
