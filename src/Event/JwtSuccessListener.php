<?php

namespace App\Event;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class JwtSuccessListener
{
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        $token = $event->getData();

        $responseTimestamp = date('Y-m-d H:i:s');

        $event->setData(
            [
                'message' => 'User logged successfully',
                'statusCode' => $event->getResponse()->getStatusCode(),
                'data' => [
                    'user' => $user,
                    'access_token' => $token['token'],
                ],
                'timestamp' => $responseTimestamp,
            ]
        );
    }
}
