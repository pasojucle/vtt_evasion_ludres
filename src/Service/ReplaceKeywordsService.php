<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\HealthDto;
use App\Dto\IdentityDto;
use App\Dto\LicenceDto;
use App\Dto\UserDto;
use App\Entity\RegistrationStep;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReplaceKeywordsService
{
    public function __construct(
        private TranslatorInterface $translator,
        private RequestStack $requestStack,
    ) {
    }

    public function replace(?string $content, ?UserDto $user = null, int $render = RegistrationStep::RENDER_VIEW, array $additionalParams = []): null|string|array
    {
        if (null !== $content) {
            if (RegistrationStep::RENDER_FILE === $render) {
                $content = $this->createPageBreak($content);
                $content = $this->removeButton($content);
            }
            if (null !== $content) {
                $keyWords = $this->getKeyWords($content);
                $content = str_replace($keyWords, $this->getReplace($keyWords, $user, $render, $additionalParams), $content);
                $content = $this->splitHeaderAndFooter($content);
            }
        }

        return $content;
    }

    public function replaceWhithParams(string $content, array $parrams)
    {
        $keyWords = $this->getKeyWords($content);
        return str_replace($keyWords, $parrams, $content);
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

    private function getKeyWords(string $content): array
    {
        if (preg_match_all('#({{ [a-zA-Z_]+ }})#', $content, $matches, PREG_PATTERN_ORDER)) {
            return $matches[1];
        };
        return [];
    }

    private function getReplace(array $keyWords, ?UserDto $user, int $render, array $additionalParams): array
    {
        $replace = [];
        /**
        *   @var ?LicenceDto $licence
        *   @var ?IdentityDto $kinship
        *   @var ?HealthDto $health
        */
        list($licence, $kinship, $health) = $this->getUserRelations($user);
        foreach ($keyWords as $keyWord) {
            $replace[] = match ($keyWord) {
                '{{ prenom_nom }}' => $user->member->fullName,
                '{{ adresse }}' => $this->getAddress($user->member),
                '{{ date_naissance }}', '{{ date_naissance_enfant }}' => $this->getDtoProperty($user->member, 'birthDate'),
                '{{ lieu_naissance }}' => $this->getDtoProperty($user->member, 'birthPlace'),
                '{{ saison }}' => $licence?->shortSeason,
                '{{ full_saison }}' => $licence?->fullSeason,
                '{{ numero_licence }}' => $this->getDtoProperty($user, 'licenceNumber'),
                '{{ cotisation }}' => $licence?->amount['str'],
                '{{ date }}' => ($licence->isYearly) ? $licence?->createdAt : $licence?->testingAt,
                '{{ prenom_nom_parent }}' => $kinship?->fullName,
                '{{ date_naissance_parent }}' => $kinship?->birthDate,
                '{{ adresse_parent }}' => $this->getAddress($user->kinship),
                '{{ prenom_nom_enfant }}' => $user->member->fullName,
                '{{ saut_page }}', '<p>&nbsp;</p>' => '<br>',
                '{{ titre_licence }}' => $licence?->registrationTitle,
                '{{ type_assurance }}' => $this->translator->trans($licence?->coverageStr),
                '{{ VTTAE }}' => ($licence?->isVae) ? 'Oui' : 'Non',
                '{{ necessite_sertificat_medical }}' => $health?->isMedicalCertificateRequired,
                '{{ montant }}' => $licence?->amount['value']?->toString(),
                '{{ autorisation_droit_image }}' => $this->getRightToTheImage($user, $render),
                '{{ saison_actuelle }}' => $this->requestStack->getSession()->get('currentSeason'),
                '{{ email_principal }}' => $user->mainEmail,
                '{{ nom_domaine }}' => $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost(),
                default => (array_key_exists($keyWord, $additionalParams)) ? $additionalParams[$keyWord] : $keyWord
            };
        }

        return $replace;
    }

    private function getUserRelations(?UserDto $user): array
    {
        if (!$user) {
            return [null, null, null];
        }

        return [$this->getDtoProperty($user, 'lastLicence'), $this->getDtoProperty($user, 'kinship'), $this->getDtoProperty($user, 'health'), ];
    }


    private function getAddress(?IdentityDto $identity): string
    {
        if (!$identity) {
            return '';
        }

        return((new ReflectionProperty($identity, 'address'))->isInitialized($identity)) ? $identity->address->toString() : '';
    }

    private function getDtoProperty(UserDto|IdentityDto|LicenceDto $dto, string $property): string|array|null|LicenceDto|HealthDto|IdentityDto
    {
        return (new ReflectionProperty($dto, $property))->isInitialized($dto) ? $dto->$property : '';
    }

    private function getRightToTheImage(UserDto $user, int $render): string
    {
        $authorizations = $this->getDtoProperty($user->lastLicence, 'authorizations');
        if (!$authorizations) {
            return '';
        }

        return (RegistrationStep::RENDER_FILE === $render) ? sprintf('<b>%s</b>', $authorizations['rightToTheImage']?->toString) : 'autorise';
    }

    public function replaceCurrentSaison(string|bool|array|int|null $content): string|bool|array|int|null
    {
        if (is_string($content)) {
            $session = $this->requestStack->getSession();
            return str_replace('{{ saison_actuelle }}', (string) $session->get('currentSeason'), $content);
        }

        return $content;
    }

    public function replaceUserFullName(string|bool|array|int|null $content, UserDto $user): string|bool|array|int|null
    {
        if (is_string($content)) {
            return str_replace('{{ prenom_nom }}', (string) $user->member->fullName, $content);
        }

        return $content;
    }
}
