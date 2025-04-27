<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\ApiResource\Action;
use App\Dto\ActionDto;
use App\Entity\Message;
use App\Entity\Parameter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActionDtoTransformer
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function fromMessage(Message $message): ActionDto
    {
        $actionDto = new ActionDto();
        $actionDto->url = $this->urlGenerator->generate('admin_message_edit_content', ['message' => $message->getId()]);
        $actionDto->label = $message->getLabel();
        $actionDto->icon = 'fa-regular fa-message';
        $actionDto->openInModal = true;

        return $actionDto;
    }

    public function fromParameter(Parameter $parameter): ActionDto
    {
        $actionDto = new ActionDto();
        $actionDto->url = $this->urlGenerator->generate('admin_parameter_edit', ['name' => $parameter->getId()]);
        $actionDto->label = $parameter->getLabel();
        $actionDto->icon = 'fas fa-sliders-h';
        $actionDto->openInModal = true;

        return $actionDto;
    }

    public function fromAction(Action $action): ActionDto
    {
        $actionDto = new ActionDto();
        $routeName = $action->getClassRoute() . $action->getMethodRoute();
        $actionDto->url = $this->urlGenerator->generate($routeName);
        $actionDto->label = $this->translator->trans(sprintf('content.route.%s', $routeName));
        $actionDto->icon = $action->getIcon();

        return $actionDto;
    }
}
