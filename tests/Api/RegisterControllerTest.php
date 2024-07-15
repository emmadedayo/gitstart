<?php

namespace App\Test\Integration;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegisterControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    public const APPLICATION_JSON = 'application/json';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testCreateAccount(): void
    {
        $this->createAccountRequest([
            'password' => '1234',
            'email' => 'manager@gitstart.com',
            'fullName' => 'Gitstart',
        ]);

        $this->assertEquals(JsonResponse::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $responseContent);
        $this->assertArrayHasKey('auth_token', $responseContent['data']);
        $this->assertArrayHasKey('user', $responseContent['data']);
        $this->assertArrayHasKey('fullName', $responseContent['data']['user']);
        $this->assertEquals('manager@gitstart.com', $responseContent['data']['user']['email']);
        $this->assertEquals('Account created successfully', $responseContent['message']);
    }

    public function testCreateAlreadyExistAccount(): void
    {
        $this->createAccountRequest([
            'password' => '1234',
            'email' => 'admin@gitstart.com',
            'fullName' => 'Gitstart',
        ]);

        $this->assertEquals(JsonResponse::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('User with same email already exist', $responseContent['message']);
    }

    public function testCreateAccountValidation(): void
    {
        $this->createAccountRequest([
            'password' => '1234',
            'email' => 'sales@gitstart.com',
        ]);

        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Validation error occurred', $responseContent['message']);
        $this->assertArrayHasKey('errors', $responseContent);
        $this->assertContains('Fullname is required', $responseContent['errors']);
    }

    private function createAccountRequest(array $data): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            [
                'CONTENT_TYPE' => self::APPLICATION_JSON,
            ],
            json_encode($data)
        );
    }
}
