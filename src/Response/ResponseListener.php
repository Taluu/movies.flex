<?php
namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

use App\MovieNotFoundException;

class ResponseListener
{
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $value = $event->getControllerResult();
        $data = is_iterable($value) ? $value : [$value];

        $data = [
            'total' => count($value),
            'count' => count($value),
            'data' => $this->serializer->normalize($data)
        ];

        if (!is_iterable($value)) {
            $data['data'] = array_pop($data['data']);
        }

        $event->setResponse(new JsonResponse($data, 200));
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();

        if (!$e instanceof MovieNotFoundException) {
            return;
        }

        $event->setResponse(new JsonResponse(['error' => $e->getMessage()], 404));
    }
}
