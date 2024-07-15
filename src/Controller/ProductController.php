<?php

namespace App\Controller;

use App\Dto\ProductDTO;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    private ProductService $productService;
    private SerializerInterface $serializer;
    private Security $security;

    public function __construct(ProductService $productService, SerializerInterface $serializer, Security $security)
    {
        $this->productService = $productService;
        $this->serializer = $serializer;
        $this->security = $security;
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('', name: 'product_create', methods: ['POST'])]
    public function createProduct(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $productDTO = $this->serializer->deserialize($data, ProductDTO::class, 'json');
        $this->productService->createProduct($productDTO);

        return new JsonResponse(['message' => 'Product created successfully', 'is_success' => true, 'status_code' => JsonResponse::HTTP_CREATED], 201);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('', name: 'product_list', methods: ['GET'])]
    public function listProducts(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $products = $this->productService->listProducts($page, $limit);

        return new JsonResponse([
            'message' => 'Products fetched successfully',
            'is_success' => true,
            'data' => $products['products'],
            'meta' => [
                'total' => $products['total'],
                'page' => $products['page'],
                'limit' => $products['limit'],
            ],
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function showProduct(string $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);
        $data = $this->serializer->serialize($product, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            },
            'ignored_attributes' => ['password', 'roles', 'userIdentifier'],
        ]);

        return new JsonResponse(['is_success' => true, 'status_code' => JsonResponse::HTTP_OK, 'message' => 'Product fetched successfully', 'data' => json_decode($data)]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}', name: 'product_update', methods: ['PUT'])]
    public function updateProduct(string $id, Request $request): JsonResponse
    {
        $product = $this->productService->getProductById($id);
        $data = $request->getContent();
        $productDTO = $this->serializer->deserialize($data, ProductDTO::class, 'json');
        $this->productService->updateProduct($product, $productDTO);

        return new JsonResponse(['message' => 'Product updated successfully', 'is_success' => true, 'status_code' => JsonResponse::HTTP_OK]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function deleteProduct(string $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);

        $this->productService->deleteProduct($product);

        return new JsonResponse(['message' => 'Product deleted successfully', 'is_success' => true, 'status_code' => JsonResponse::HTTP_OK]);
    }
}
