<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PasskeyValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        $errors = [];

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match('/^[A-Za-zæøåÆØÅ]+$/', $value, $matches)) {
            $errors[] = $value;
        }

        if (!empty($errors)) {
            $this->context->buildViolation($constraint->message)
              ->setParameter('{{ errors }}', implode(', ', $errors))
              ->addViolation();
        }
    }
}
