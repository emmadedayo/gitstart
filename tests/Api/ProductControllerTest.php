<?php

namespace App\Tests\Integration;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
{
    private $client;
    private $authToken;
    private $productRepository;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->productRepository = static::getContainer()->get(ProductRepository::class);
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->authenticateUser();
    }

    private function authenticateUser(): void
    {
        $credentials = [
            'email' => 'olufemi_adeninuola@example.com',
            'password' => 'password',
        ];
        $this->client->request(Request::METHOD_POST, '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($credentials));

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful(); // Ensure authentication success
        $this->authToken = json_decode($response->getContent(), true)['data']['token'];
    }

    public function testCreateProduct(): void
    {
        $faker = Factory::create();
        $productData = [
            'productName' => 'Ultra HD Smart TV',
            'productPrice' => '1000.00',
            'productDescription' => 'Ultra HD Smart TV with 4K resolution',
            'productImage' => 'https://example.com/image.jpg',
        ];

        $this->client->request(
            Request::METHOD_POST,
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken],
            json_encode($productData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Product created successfully', $responseContent['message']);
    }

    public function testCreateProductValidation(): void
    {
        $productData = [
            'productName' => 'Ultra HD Smart TV',
            'productPrice' => '1000.00',
            'productDescription' => 'Ultra HD Smart TV with 4K resolution',
        ];

        $this->client->request(
            Request::METHOD_POST,
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken],
            json_encode($productData)
        );

        $this->assertResponseStatusCodeSame(400);
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid input. Field productImage: This value should not be blank. ', $responseContent['message']);
    }

    public function testGetProductById(): void
    {
        $prd = $this->productRepository->findOneBy(['product_name' => 'Apple iPhone 13 Pro']);
        $this->client->request(
            Request::METHOD_GET,
            '/api/products/' . $prd->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken]
        );

        $this->assertResponseIsSuccessful();
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Apple iPhone 13 Pro', $responseContent['data']['productName']);
    }

    public function testGetAllProducts(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken]
        );

        $this->assertResponseIsSuccessful();
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $responseContent);
        $this->assertGreaterThan(0, count($responseContent['data']));
    }
}
