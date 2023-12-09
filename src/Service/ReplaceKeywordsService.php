<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserDto;
use App\Entity\RegistrationStep;
use DateTime;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReplaceKeywordsService
{
    public function __construct(
        private TranslatorInterface $translator,
        private RequestStack $requestStack,
    ) {
    }

    public function replace(UserDto $user, ?string $content, int $render = RegistrationStep::RENDER_VIEW): null|string|array
    {
        if (null !== $content) {
            if (RegistrationStep::RENDER_FILE === $render) {
                $content = $this->createPageBreak($content);
                $content = $this->removeButton($content);
            }
            if (null !== $content) {
                $content = str_replace($this->search(), $this->replaces($user, $render), $content);
                $content = $this->splitHeaderAndFooter($content);
            }
        }

        return $content;
    }

    private function createPageBreak(string $content): string
    {
        $pages = preg_split('#{{ saut_page }}#', $content);
        if (1 < count($pages)) {
            $content = '';
            foreach ($pages as $page) {
                $content .= '<div class="page_break">' . $page . '</div>';
            }
        }

        return $content;
    }

    private function splitHeaderAndFooter(string $content): string|array
    {
        $pattern = '#^<p>{{ entete }}</p>([\s\w\W]*)<p>{{ entete }}</p>([\s\w\W]*)<p>{{ pied_page }}</p>([\s\w\W]*)<p>{{ pied_page }}</p>$#im';
        preg_match($pattern, $content, $matches);
        if (!empty($matches)) {
            return [
                'header' => $matches[1],
                'footer' => $matches[3],
            ];
        }

        return $content;
    }

    private function removeButton(string $content): ?string
    {
        $pattern = '#<p>{{ bouton\w+ }}</p>#i';
        $content = preg_replace($pattern, '', $content);

        if (empty($content)) {
            $content = null;
        }

        return $content;
    }

    private function search(): array
    {
        return [
            '{{ prenom_nom }}',
            '{{ adresse }}',
            '{{ date_naissance }}',
            '{{ lieu_naissance }}',
            '{{ saison }}',
            '{{ full_saison }}',
            '{{ numero_licence }}',
            '{{ cotisation }}',
            '{{ date }}',
            '{{ prenom_nom_parent }}',
            '{{ date_naissance_parent }}',
            '{{ adresse_parent }}',
            '{{ prenom_nom_enfant }}',
            '{{ date_naissance_enfant }}',
            '{{ saut_page }}',
            '{{ titre_licence }}',
            '{{ type_assurance}}',
            '{{ VTTAE }}',
            '{{ necessite_sertificat_medical }}',
            '{{ attestations_sur_honneur }}',
            '{{ montant }}',
            '<p>&nbsp;</p>',
            '{{ autorisation_droit_image }}',
            '{{ saison_actuelle }}',
            '{{ email_principal }}',
        ];
    }

    private function replaces(UserDto $user, int $render): array
    {
        $address = $user->member->address->toString();
        $kinshipAddress = $user->kinship?->address->toString();
        $today = new DateTime();
        $licence = $user->lastLicence;

        return [
            $user->member->fullName,
            $address,
            $user->member->birthDate,
            $user->member->birthPlace,
            $licence->shortSeason,
            $licence->fullSeason,
            $user->licenceNumber,
            $licence->amount['str'],
            $today->format('d/m/Y'),
            $user->kinship?->fullName,
            $user->kinship?->birthDate,
            $kinshipAddress,
            $user->member->fullName,
            $user->member->birthDate,
            '<br>',
            $licence->registrationTitle,
            $this->translator->trans($licence->coverageStr),
            ($licence->isVae) ? 'Oui' : 'Non',
            $user->health->isMedicalCertificateRequired,
            $licence->licenceSwornCertifications,
            $licence->amount['value']?->toString(),
            '<br>',
            (RegistrationStep::RENDER_FILE === $render) ? sprintf('<b>%s</b>', $user->approvals['rightToTheImage']->toString) : 'autorise',
            $this->requestStack->getSession()->get('currentSeason'),
            $user->mainEmail,
        ];
    }
}
