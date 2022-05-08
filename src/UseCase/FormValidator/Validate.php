<?php

declare(strict_types=1);

namespace App\UseCase\FormValidator;

use App\Validator\BirthDate;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class Validate
{
    public function __construct(
        private ValidatorInterface $validator,
        private Environment $twig
    ) {
    }

    public function execute(Request $request): array
    {
        $current = $request->request->get('current');
        $value = ($request->request->all('values')) ? $request->request->all('values') : $request->request->get('value');
        $value = $this->getValue($value, $current);
        $constraintClass = $request->request->get('constraint');
        $required = !empty($request->request->get('required'));

        $constraints = $this->getConstraints($constraintClass, $required, $value);

        $violations = $this->validator->validate($value, $constraints);
        $render = $this->twig->render('validator/errors.html.twig', [
            'violations' => $violations,
        ]);

        $status = $this->getStatus($violations, empty($value));

        return [
            'status' => $status,
            'html' => $render,
            'multiple' => is_array($value),
        ];
    }

    private function getConstraints(?string $constraintClass, bool $required, array|string &$value): array
    {
        $constraints = [];
        if (!empty($constraintClass)) {
            list($namespace, $constraintClass) = explode('-', $constraintClass);
            $constraintClass = ('symfony' === $namespace)
                ? 'Symfony\Component\Validator\Constraints\\'.$constraintClass
                : 'App\Validator\\'.$constraintClass;
            $constraint = new $constraintClass();
            $constraints[] = $constraint;
            if ($constraint instanceof BirthDate) {
                $value = DateTime::createFromFormat('d/m/Y', $value);
            }
        }
        if ($required) {
            $constraints[] = new NotBlank();
        }

        return $constraints;
    }

    private function getStatus(ConstraintViolationListInterface $violations, bool $isEmptyValue): ?string
    {
        if (!empty((string) $violations)) {
            $status = 'ALERT_WARNING';
        } elseif (empty((string) $violations) && !$isEmptyValue) {
            $status = 'SUCCESS';
        } else {
            $status = null;
        }

        return $status;
    }

    private function getValue(array|string $value, string $current): array|string
    {
        if (is_string($value)) {
            return $value;
        }
        $values = [];
        foreach ($value as $name => $val) {
            if ($name === $current && empty($val)) {
                return $val;
            }
            if ($name === $current || !empty($val)) {
                $values[$name] = $val;
            }
        }
        if (1 < count($values)) {
            return $values;
        }

        return array_shift($values);
    }
}
