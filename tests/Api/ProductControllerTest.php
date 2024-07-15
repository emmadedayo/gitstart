<?php

namespace App\Test\Integration;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    public const APPLICATION_JSON = 'application/json';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->authenticateUser();
    }

    private function authenticateUser(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => self::APPLICATION_JSON],
            json_encode([
                'email' => 'admin@gitstart.com',
                'password' => 'password',
            ])
        );
    }

    public function testCreateProduct(): void
    {
        $token = $this->getAuthToken();

        $this->client->request(
            'POST',
            '/api/products',
            [],
            [],
            [
                'CONTENT_TYPE' => self::APPLICATION_JSON,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
            json_encode([
                'name' => 'Gucci Black Jeans',
                'price' => 3000,
                'description' => 'This is a nice gucci black jeans',
            ])
        );

        $this->assertEquals(JsonResponse::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
        $this->assertEquals('Gucci Black Jeans', $responseContent['data']['name']);
        $this->assertEquals('Product created successfully', $responseContent['message']);
    }

    public function testCreateProductValidationErrors(): void
    {
        $token = $this->getAuthToken();

        $this->client->request(
            'POST',
            '/api/products',
            [],
            [],
            [
                'CONTENT_TYPE' => self::APPLICATION_JSON,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
            json_encode([
                'name' => '',
                'description' => '',
                'price' => 1000,
            ])
        );

        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $responseContent);
        $this->assertCount(2, $responseContent['errors']);
        $this->assertContains('Name is required', $responseContent['errors']);
        $this->assertContains('Description is required', $responseContent['errors']);
    }

    public function testEditProduct(): void
    {
        $token = $this->getAuthToken();
        $product = $this->createProduct();

        $this->client->request(
            'PUT',
            '/api/products/' . $product->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => self::APPLICATION_JSON,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
            json_encode([
                'name' => 'Turkey Eggs',
                'price' => 600,
                'description' => 'This egg is good for your health',
            ])
        );

        $this->assertEquals(JsonResponse::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Product updated successfully', $responseContent['message']);
        $this->assertEquals('Turkey Eggs', $responseContent['data']['name']);
    }

    public function testEditProductValidation(): void
    {
        $token = $this->getAuthToken();

        $this->client->request(
            'PUT',
            '/api/products/5',
            [],
            [],
            [
                'CONTENT_TYPE' => self::APPLICATION_JSON,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
            json_encode([
                'name' => 'Turkey Eggs',
                'price' => 600,
            ])
        );

        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Product not found', $responseContent['message']);
    }

    public function testFindOneProduct(): void
    {
        $token = $this->getAuthToken();
        $product = $this->createProduct();

        $this->client->request(
            'GET',
            '/api/products/' . $product->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => self::APPLICATION_JSON,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]
        );

        $this->assertEquals(JsonResponse::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Product fetched successfully', $responseContent['message']);
        $this->assertArrayHasKey('data', $responseContent);
        $this->assertEquals('Egg', $responseContent['data']['name']);
    }

    public function testFindOneProductNotFound(): void
    {
        $token = $this->getAuthToken();

        $this->client->request(
            'GET',
            '/api/products/1',
            [],
            [],
            [
                'CONTENT_TYPE' => self::APPLICATION_JSON,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]
        );

        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Product not found', $responseContent['message']);
    }

    public function testDeleteProduct(): void
    {
        $productRepository = static::getContainer()->get(ProductRepository::class);
        $oldProducts = $productRepository->findAll();

        $token = $this->getAuthToken();
        $product = $this->createProduct();

        $this->client->request(
            'DELETE',
            '/api/products/' . $product->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => self::APPLICATION_JSON,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]
        );

        $this->assertEquals(JsonResponse::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $newProducts = $productRepository->findAll();

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
        $this->assertEquals(true, $responseContent['data']);
        $this->assertEquals('Product deleted successfully', $responseContent['message']);
        $this->assertEquals(count($oldProducts), count($newProducts));
    }

    public function testDeleteProductValidation(): void
    {
        $token = $this->getAuthToken();

        $this->client->request(
            'DELETE',
            '/api/products/1',
            [],
            [],
            [
                'CONTENT_TYPE' => self::APPLICATION_JSON,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]
        );

        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Product not found', $responseContent['message']);
    }

    public function testFindAll(): void
    {
        $token = $this->getAuthToken();

        $this->client->request(
            'GET',
            '/api/products',
            [],
            [],
            [
                'CONTENT_TYPE' => self::APPLICATION_JSON,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]
        );

        $this->assertEquals(JsonResponse::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Products fetched successfully', $responseContent['message']);
        $this->assertArrayHasKey('data', $responseContent);
    }

    private function getAuthToken(): string
    {
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        return $responseContent['data']['access_token'];
    }

    private function createProduct(string $name = 'Egg', int $price = 400, string $description = 'This is egg description'): Product
    {
        $product = new Product();
        $product->setName($name);
        $product->setPrice($price);
        $product->setDescription($description);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($product);
        $entityManager->flush();

        return $product;
    }
}
