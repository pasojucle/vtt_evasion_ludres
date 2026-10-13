<?php

declare(strict_types=1);

namespace App\State\Certificate\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\MemberStatus\MemberStatusSheetView;
use App\Entity\Member;

/**
 * @implements FormComponentProviderInterface<Member>
 */
class MemberCertificateCreateProvider implements FormComponentProviderInterface
{
    public function getView(object $data, ?HandlerContext $context = null): MemberStatusSheetView
    {
        $identity = $data->getIdentity();

        return new MemberStatusSheetView(
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
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ],
        ];
    }
}
