<?php

namespace App\Event;

use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'handleException',
        ];
    }

    public function handleException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Customize handling for specific exceptions
        if ($exception instanceof NotFoundHttpException) {
            $message = 'Route not found.';
            $statusCode = 404;
        } elseif ($exception instanceof UniqueConstraintViolationException) {
            $statusCode = 422; // Unprocessable Entity
            $message = 'Duplicate entry violation';
        } elseif ($exception instanceof ConstraintViolationListInterface) {
            $statusCode = 422;
            $message = 'Validation failed';
        } elseif ($exception instanceof HttpExceptionInterface) {
            $message = $exception->getMessage();
            $statusCode = $exception->getStatusCode();
        } elseif ($exception instanceof ConnectionException) {
            $message = $exception->getMessage();
            $statusCode = 500;
        } else {
            $message = 'Something went wrong.';
            $statusCode = 500;
        }
        // Create JSON response
        $response = new JsonResponse([
            'message' => $message,
            'is_success' => false,
            'status_code' => $statusCode,
            'details' => $this->getExceptionDetails($exception),
        ], $statusCode);

        // Set the response to the event
        $event->setResponse($response);
    }

    private function getExceptionDetails(\Throwable $exception): array
    {
        $details = [];

        // If in dev environment, include exception trace
        if ('dev' === $_SERVER['APP_ENV']) {
            $details['trace'] = $exception->getTrace();
        }

        if ($exception instanceof ConstraintViolationListInterface) {
            $details['violations'] = [];
            foreach ($exception as $violation) {
                $details['violations'][] = $violation->getMessage();
            }
        }

        return $details;
    }
}
