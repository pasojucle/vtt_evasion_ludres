<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use ReflectionClass;
use Twig\Environment;
use App\Entity\UserSkill;
use App\Form\Admin\UserSkillType;
use Symfony\Component\Form\FormView;
use Doctrine\ORM\PersistentCollection;
use function Symfony\Component\String\u;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Dto\DtoTransformer\DtoTransformerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserSkillService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly TranslatorInterface $translator,
        private readonly ApiService $api,
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {
        
    }

    public function getList(User $user): JsonResponse
    {
        $userSkills = [];
        foreach($user->getUserSkills() as $userSkill) {
            $form = $this->formFactory->create(UserSkillType::class, $userSkill, [
                'text_type' => UserSkillType::BY_SKILLS,
                'action' => $this->urlGenerator->generate('api_user_skill_edit', ['userSkill' => $userSkill->getId()])
            ]);
            $userSkills[] = $this->getUserSkill($form->createView());
        }

        return new JsonResponse(['list' => $userSkills]);
    }

    public function render(FormInterface $form): JsonResponse
    {
        foreach($form->createView()->getIterator() as $child) {
            $blockPrefix = $this->api->getBlockPrefix($child);
            ('_token' === $child->vars['name'])
                ? $this->addToken($child->vars, $blockPrefix, $data)
                : $this->addList($child, $blockPrefix, $data);
        }

        return new JsonResponse($data);
    }

    private function addToken(Array $vars, string $blockPrefix, array &$data): void
    {
        $data['token'] = $this->api->getProps($vars, $blockPrefix);
    }

    private function addList(FormView $form, string $blockPrefix, array &$data): void
    {
        if ('collection' === $blockPrefix) {
            foreach($form->children as $entryKey => $entry) {
                $data['list'][$entry->vars['name']] = [
                    'id' => $entry->vars['data']->getId(),
                    'action' => $this->urlGenerator->generate('api_user_skill_edit', ['userSkill' => $entry->vars['data']->getId()])
                ];
                foreach($entry->children as $entryChild) {
                    if ($entryChildBlocPrefix = $this->api->getBlockPrefix($entryChild)) {
                        $props = $this->api->getProps($entryChild->vars, $entryChildBlocPrefix, $entryKey);
                        $props['row_attr'] = $entryChild->vars['row_attr'];
                        $data['list'][$entry->vars['name']][$entryChild->vars['name']] = $props;
                    }
                }
            }
        }
    }

    

    public function getUserSkill(FormView $form): array
    {
        $userSkillEntity = $form->vars['value'];
        $skill = $userSkillEntity->getSkill();
        $userSkill = [
            'id' => $userSkillEntity->getId(),
            'action' => $form->vars['action'],
            'category' => ['id' => $skill->getCategory()->getId()],
            'level' => ['id' => $skill->getLevel()->getId()],
        ];

        foreach($form->children as $entryChild) {
            if ($entryChildBlocPrefix = $this->api->getBlockPrefix($entryChild)) {
                $props = $this->api->getProps($entryChild->vars, $entryChildBlocPrefix, $userSkillEntity->getId());
                $props['row_attr'] = $entryChild->vars['row_attr'];
                $userSkill[$entryChild->vars['name']] = $props;
            }
        }

        return $userSkill;
    }
}