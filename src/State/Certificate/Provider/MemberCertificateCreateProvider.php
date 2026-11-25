<?php

declare(strict_types=1);

namespace App\State\Certificate\Provider;

use App\Core\Contract\Provider\DataInitializerInterface;
use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\StreamDownloadProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Payload\MemberCertificat;
use App\Dto\View\SheetView;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Member;
use App\Mapper\User\MemberRegistrationCertificateMapper;
use App\Service\MessageService;
use App\Service\Placeholder\PlaceholderResolver;

/**
 * @implements FormComponentProviderInterface<MemberCertificat>
 * @implements StreamDownloadProviderInterface<MemberCertificat>
 */
class MemberCertificateCreateProvider implements FormComponentProviderInterface, DataInitializerInterface, StreamDownloadProviderInterface
{
    public function __construct(
        private MessageService $messageService,
        private PlaceholderResolver $placeholderResolver,
        private MemberRegistrationCertificateMapper $memberRegistrationCertificateMapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        $identity = $data->member->getIdentity();

        return new SheetView(
            title: 'Attestation d\'inscription CE',
            description: sprintf(
                'Compléter et générer une attestion d\'inscription pour les commités d\'entreprise au nom de %s au format pdf',
                $identity->getFullName()
            ),
            action: 'Génerer'
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-turbo' => 'false',
                'data-action' => 'submit->sheet#handleDownloadSubmit',
            ],
        ];
    }

    /**
     * @param Member $data
     */
    public function initializeData(object $data): object
    {
        $licence = $data->getLastLicence();
        $rawContent = (LicenceCategoryEnum::ADULT === $licence->getCategory())
            ? $this->messageService->getMessageById('REGISTRATION_CERTIFICATE_ADULT')
            : $this->messageService->getMessageById('REGISTRATION_CERTIFICATE_SCHOOL');

        $content = $this->placeholderResolver->resolve($rawContent, $data, $licence, $data->getLegalGardian());

        return new MemberCertificat($data, $content);
    }

    public function streamDownloadContent(?object $data, ?HandlerContext $context = null): void
    {
        $this->memberRegistrationCertificateMapper->streamToPdf($data->content);
    }

    public function basename(?object $data, ?HandlerContext $context = null): string
    {
        return sprintf('Attestation_inscription_%s.pdf', $data->member->getLicenceNumber());
    }

    public function contentType(): string
    {
        return 'application/pdf';
    }
}
