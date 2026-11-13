<?php

declare(strict_types=1);

namespace App\Core\Dto;

use App\Core\Contract\PayloadInterface;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;

readonly class ActionPayload implements PayloadInterface
{
    public function __construct(
        public object $data,
        public array $files = [],
        Public ?SubmitButton $clickedButton = null,
    ) {
    }

    public static function create(FormInterface $form, Request $request): self
    {
        $clickedButton = null;
        foreach ($form->all() as $child) {
            if ($child instanceof ClickableInterface && $child instanceof SubmitButton && $child->isClicked()) {
                $clickedButton = $child;
                break; 
            }
        }

        return new self(
            $form->getData(),
            $request->files->get($form->getName(), []),
            $clickedButton,
        );
    }
}
