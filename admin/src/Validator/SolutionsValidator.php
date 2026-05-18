<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class SolutionsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        $errors = [];
        foreach ($value as $input) {
            if (!is_string($input)) {
                // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
                throw new UnexpectedValueException($input, 'string');
            }

            if (!preg_match('/^[\\w?+*-\/_=<>.: æøåÆØÅ]+$/', $input, $matches)) {
                $errors[] = $input;
            }
        }

        if (!empty($errors)) {
            if ($constraint instanceof Solutions) {
                $this->context->buildViolation($constraint->message)
                  ->setParameter('{{ errors }}', implode(', ', $errors))
                  ->addViolation();
            }
        }
    }
}
