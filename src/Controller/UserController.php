<?php

namespace App\Controller;

use App\Dto\UserLoginDTO;
use App\Dto\UserRegistrationDTO;
use App\Service\DTOValidator;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    private DTOValidator $dtoValidator;
    private SerializerInterface $serializer;
    private UserService $userService;

    public function __construct(DTOValidator $dtoValidator, SerializerInterface $serializer, UserService $userService)
    {
        $this->dtoValidator = $dtoValidator;
        $this->serializer = $serializer;
        $this->userService = $userService;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $dto = $this->serializer->deserialize($data, UserRegistrationDTO::class, 'json');
        $this->dtoValidator->validate($dto, ['registration']);
        $this->userService->registerUser($dto);

        return new JsonResponse(
            [
            'message' => 'User registered successfully',
            'is_success' => false,
            'status_code' => JsonResponse::HTTP_CREATED],
            JsonResponse::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        $data = $request->getContent();
        $dto = $this->serializer->deserialize($data, UserLoginDTO::class, 'json');
        $this->dtoValidator->validate($dto);

        $user = $this->userService->findUserByEmail($dto->email);

        if (!$user || !$passwordHasher->isPasswordValid($user, $dto->password)) {
            return new JsonResponse(
                [
                'message' => 'Invalid credentials',
                'is_success' => false,
                'status_code' => JsonResponse::HTTP_CREATED],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $token = $JWTManager->create($user);
        $response = [
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ],
        ];

        return new JsonResponse(
            [
            'message' => 'Login successful',
            'is_success' => true,
            'data' => $response,
            'status_code' => JsonResponse::HTTP_OK],
            JsonResponse::HTTP_OK
        );
    }
}
