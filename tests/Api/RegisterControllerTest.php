<?php
namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Faker\Factory;

class RegisterControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testSuccessfulAccountCreation()
    {
        $faker = Factory::create();
        $response = $this->registerUser([
            'email' => $faker->unique()->safeEmail,
            'password' => '1234',
            'fullname' => $faker->name,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('User registered successfully', $responseData['message']);
    }

    public function testExistingAccountRegistration()
    {
        $response = $this->registerUser([
            'email' => 'olufemi_adeninuola@example.com', // Assuming this user already exists
            'password' => '1234',
            'fullname' => 'Olufemi Adeninuola',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $this->assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Email already exist', $responseData['message']);
    }

    public function testValidationErrors()
    {
        $response = $this->registerUser([
            'email' => 'olufemi_adeninuola@example.com', // Assuming this user already exists
            'password' => '1234',
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Invalid input. Field fullname: This value should not be blank. ', $responseData['message']);
    }

    private function registerUser(array $data): Response
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        return $this->client->getResponse();
    }
}
