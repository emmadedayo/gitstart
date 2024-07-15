<?php

// Class generated by Adenagbe Emmanuel on 13/07/2024

namespace App\Service;

use App\Dto\UserRegistrationDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
    }

    public function registerUser(UserRegistrationDTO $dto): void
    {
        $this->throwIfUserAlreadyExists($dto->email);
        $user = new User();
        $user->setFullname($dto->fullname);
        $user->setEmail($dto->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    private function throwIfUserAlreadyExists(string $userEmail): void
    {
        // make sure user with same email does not exist
        $oldUser = $this->userRepository->findOneBy(['email' => $userEmail]);

        if ($oldUser) {
            throw new ConflictHttpException('Email already exist');
        }
    }
}
