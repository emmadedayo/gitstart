<?php

// src/Service/ProductService.php

namespace App\Service;

use App\Dto\ProductDTO;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Knp\Component\Pager\PaginatorInterface;

class ProductService
{
    private EntityManagerInterface $entityManager;
    private DTOValidator $dtoValidator;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $entityManager, DTOValidator $dtoValidator, PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        $this->dtoValidator = $dtoValidator;
        $this->paginator = $paginator;
    }

    public function createProduct(ProductDTO $dto, $user): Product
    {
        $this->dtoValidator->validate($dto);

        $product = new Product();
        $product->setProductName($dto->productName);
        $product->setProductPrice($dto->productPrice);
        $product->setProductionDescription($dto->productionDescription);
        $product->setProductImage($dto->productImage);
        $product->setUser($user);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    public function getProductById(string $id): object
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            // Throw an exception if the product is not found
            throw new EntityNotFoundException(sprintf('Product with ID "%s" not found', $id));
        }

        return $product;
    }

    public function updateProduct(Product $product, ProductDTO $dto): void
    {
        $this->dtoValidator->validate($dto);

        $product->setProductName($dto->productName);
        $product->setProductPrice($dto->productPrice);
        $product->setProductionDescription($dto->productionDescription);
        $product->setProductImage($dto->productImage);

        $this->entityManager->flush();
    }

    public function deleteProduct(Product $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    public function listProducts(int $page = 1, int $limit = 10): array
    {
        $queryBuilder = $this->entityManager->getRepository(Product::class)->createQueryBuilder('p');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            $limit
        );

        $products = [];

        foreach ($pagination->getItems() as $product) {
            $products[] = [
                'id' => $product->getId(),
                'productName' => $product->getProductName(),
                'productPrice' => $product->getProductPrice(),
                'productionDescription' => $product->getProductionDescription(),
                'productImage' => $product->getProductImage(),
                'userId' => $product->getUser()->getId(),
            ];
        }

        return [
            'total' => $pagination->getTotalItemCount(),
            'page' => $pagination->getCurrentPageNumber(),
            'limit' => $pagination->getItemNumberPerPage(),
            'products' => $products,
        ];
    }
}
