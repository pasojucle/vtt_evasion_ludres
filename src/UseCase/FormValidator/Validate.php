<?php

declare(strict_types=1);

namespace App\UseCase\FormValidator;

use App\Service\ValidatorService;
use App\Validator\BirthDate;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Twig\Environment;

class Validate
{
    public function __construct(
        private ValidatorService $validator,
        private Environment $twig
    ) {
    }

    public function execute(Request $request): array
    {
        $fields = $request->request->all('validator');
        $constraintsValidator = [];
        foreach ($fields as $id => $field) {
            $value = $field['value'];
            $value = $this->getValue($value, $id);

            $constraintClass = $field['constraint'];
            $required = array_key_exists('required', $field) ? (bool)$field['required'] : false;
            $filled = array_key_exists('filled', $field) ? (bool)$field['filled'] : false;

            $constraints = $this->getConstraints($constraintClass, $required, $value, $filled);

            $violations = $this->validator->ValidateToArray($value, $constraints);
            $render = $this->twig->render('validator/errors.html.twig', [
                'violations' => $violations,
            ]);

            $status = $this->getStatus($violations, $value);

            $constraintsValidator[] = [
                'constraint' => $constraintClass,
                'id' => $id,
                'status' => $status,
                'html' => $render,
                'multiple' => is_array($value),
            ];
        }

        return  ['constraintsValidator' => $constraintsValidator];
    }

    private function getConstraints(?string $constraintClass, bool $required, array|string|null &$value, bool $filled): array
    {
        $constraints = [];
        if ($filled && !empty($constraintClass)) {
            list($namespace, $constraintClass) = explode('-', $constraintClass);
            $constraintClass = ('symfony' === $namespace)
                ? 'Symfony\Component\Validator\Constraints\\' . $constraintClass
                : 'App\Validator\\' . $constraintClass;
            $constraint = new $constraintClass();
            $constraints[] = $constraint;
            if ($constraint instanceof BirthDate) {
                $birthDateObject = DateTime::createFromFormat('d/m/Y', $value);
                if ($birthDateObject) {
                    $value = $birthDateObject;
                }
            }
        }
        if ($filled && true === $required) {
            $constraints[] = new NotBlank();
        }

        return $constraints;
    }

    private function getStatus(array $violations, array|string|null|DateTime $value): string
    {
        return match (true) {
            !empty($violations) => 'ALERT_WARNING',
            !empty($value) => 'SUCCESS',
            default => 'NOT_REQUIRED'
        };
    }

    private function getValue(array|string $value, string $id): array|string|null
    {
        if (is_string($value)) {
            return $value;
        }
        $values = [];
        $idToArray = explode('_', $id);
        $name = array_pop($idToArray);

        foreach ($value as $index => $val) {
            if ($name === $index && empty($val)) {
                return $val;
            }
            if ($name === $index || !empty($val)) {
                $values[$index] = $val;
            }
        }

        if (1 < count($values)) {
            return $values;
        }

        return array_shift($values);
    }
}
