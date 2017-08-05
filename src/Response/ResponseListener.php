<?php
namespace App\Response;

use Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

use App\MovieNotFoundException;

class ResponseListener
{
    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $value = $event->getControllerResult();
        $data = is_iterable($value) ? $value : [$value];

        $data = [
            'count' => 0,
            'total' => count($data),
            'data' => $this->normalizer->normalize($data, null, ['groups' => ['public']])
        ];

        foreach ($data['data'] as &$object) {
            if (isset($object['id'])) {
                $object['id'] = sha1($object['id']);
            }

            ++$data['count'];
        }

        if (!is_iterable($value)) {
            $data['data'] = array_pop($data['data']);
        }

        $event->setResponse(new JsonResponse($data, 200));
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();

        if ($e instanceof MovieNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if (!$e instanceof HttpExceptionInterface) {
            $e = new class($e) extends HttpException {
                public function __construct(Exception $previous)
                {
                    parent::__construct(500, 'Oops, something nasty happened !', $previous);
                }
            };
        }

        $event->setResponse(new JsonResponse(['error' => $e->getMessage(), 'http' => $e->getStatusCode()], $e->getStatusCode()));
    }
}
