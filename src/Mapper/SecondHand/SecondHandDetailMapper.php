<?php

declare(strict_types=1);

namespace App\Mapper\SecondHand;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\LinkView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\SecondHand\SecondHandDetailView;
use App\Entity\Enum\SecondHandStateEnum;
use App\Entity\SecondHand;
use App\Model\Currency;
use App\Service\UrlContextService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecondHandDetailMapper
{
    public function __construct(
        private TranslatorInterface $translator,
        private UrlContextService $urlContextService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function mapToView(SecondHand $secondHand, array $images, string $defaultImage, string $currentRoute, ?string $listRoute): SecondHandDetailView
    {
        $referer = $this->urlContextService->generateTargetUrl($currentRoute, ['secondHand' => $secondHand->getId()]);

        $identity = $secondHand->getMember()->getIdentity();
        $category = $secondHand->getCategory();
        $state = $secondHand->getState();

        return new SecondHandDetailView(
            id: $secondHand->getId(),
            name: $secondHand->getName(),
            content: $secondHand->getContent(),
            price: (new Currency($secondHand->getPrice()))->__toString(),
            categoryBadge: new BadgeView(
                $category->getIcon(),
                ColorVariant::ACCENT,
                Size::ICON,
            ),
            categoryName: $category->getName(),
            createdAt: $secondHand->getCreatedAt()->format('d/m/Y'),
            state: new BadgeView(
                $state->trans($this->translator),
                $state->variant(),
                Size::LG,
            ),
            images: $images,
            mainImagePath: $this->getMainImagePath($images, $defaultImage),
            sellerName: $identity->getFullName(),
            sellerEmail: $identity->getEmail(),
            sellerPhone: $identity->getMobile(),
            buttonEdit: new LinkView(
                url: $this->urlContextService->generateUrl('admin_second_hand_edit', [
                    'secondHand' => $secondHand->getId(),
                ], $referer),
                label: 'Modifier',
                icon: 'lucide:pencil'
            ),
            buttonDelete: new LinkView(
                url: $this->urlContextService->generateUrl('admin_second_hand_delete', [
                    'secondHand' => $secondHand->getId(),
                ], $listRoute),
                variant: ColorVariant::DESTRUCTIVE,
                label: 'Supprimer',
                icon: 'lucide:delete',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            ),
            buttonValidate: $this->buttonValidate($secondHand),
        );
    }


    private function getMainImagePath(array $images, string $defaultImage): string
    {
        if (!empty($images)) {
            return $images[array_key_first($images)]['path'];
        }
        return $defaultImage;
    }

    private function buttonValidate(SecondHand $secondHand): ? LinkView
    {
        if (SecondHandStateEnum::DRAFT === $secondHand->getState()) {
            return new LinkView(
                url: $this->urlGenerator->generate('admin_second_hand_validate', [
                    'secondHand' => $secondHand->getId(),
                ]),
                variant: ColorVariant::SUCCESS,
                label: 'Valider',
                icon: 'lucide:square-check-big',
            );
        }

        return null;
    }
}
