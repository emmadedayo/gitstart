<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UserRegistrationDTO
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    public string $password;

    // Optional for Registration
    #[Assert\NotBlank(groups: ['registration'])] // Only for registration
    public string $name;
}
