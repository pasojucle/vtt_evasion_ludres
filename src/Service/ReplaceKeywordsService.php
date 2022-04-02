<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\RegistrationStep;
use App\ViewModel\UserViewModel;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReplaceKeywordsService
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    public function replace(UserViewModel $user, ?string $content, int $render = RegistrationStep::RENDER_VIEW): null|string|array
    {
        if (null !== $content) {
            if (RegistrationStep::RENDER_FILE === $render) {
                $content = $this->createPageBreak($content);
                $content = $this->removeButton($content);
            }
            if (null !== $content) {
                $content = str_replace($this->search(), $this->replaces($user), $content);
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
                $content .= '<div class="page_break">'.$page.'</div>';
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
            '{{ saison }}',
            '{{ numero_licence }}',
            '{{ cotisation }}',
            '{{ date }}',
            '{{ prenom_nom_parent }}',
            '{{ date_naissance_parent }}',
            '{{ prenom_nom_enfant }}',
            '{{ date_naissance_enfant }}',
            '{{ saut_page }}',
            '{{ titre_licence }}',
            '{{ type_licence }}',
            '{{ type_assurance}}',
            '{{ necessite_sertificat_medical }}',
            '<p>&nbsp;</p>',
        ];
    }

    private function replaces(UserViewModel $user): array
    {
        $address = $user->member->address->toString();
        $today = new DateTime();
        $licence = $user->seasonLicence;

        return [
            $user->member->fullName,
            $address,
            $licence->season,
            $user->getLicenceNumber(),
            $licence->amountStr,
            $today->format('d/m/Y'),
            $user->getFullName(),
            $user->getBirthDate(),
            $user->getFullNameChildren(),
            $user->getBirthDateChildren(),
            '<br>',
            $licence->getRegistrationTitle($user),
            $this->translator->trans($licence->type),
            $this->translator->trans($licence->coverageStr),
            $user->isMedicalCertificateRequired(),
            '<br>',
        ];
    }
}
