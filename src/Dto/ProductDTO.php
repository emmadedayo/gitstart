<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $productName = null;

    #[Assert\NotBlank]
    #[Assert\Type('numeric')]
    public ?string $productPrice = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $productDescription = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $productImage = null;
}
