<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DTOValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate(object $dto, array $groups = []): void
    {
        $violations = $this->validator->validate($dto, null, $groups);

        if (count($violations) > 0) {
            $message = 'Invalid input. ';
            foreach ($violations as $violation) {
                $message .= sprintf('Field %s: %s ', $violation->getPropertyPath(), $violation->getMessage());
            }
            throw new BadRequestException($message, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
