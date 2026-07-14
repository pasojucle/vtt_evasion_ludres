<?php

namespace App\Form\Type;

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TiptapType extends AbstractType
{
    protected ObjectManager $em;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Kernel $kernel,
    ) {
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'config_name',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['upload_url'] = $this->getUploadUrl();
        $view->vars['compound'] = false;
        $view->vars['enable'] = true;
        $view->vars['async'] = true;
        $view->vars['block_prefix'] = 'tiptap';
        $view->vars['toolbar'] = $this->getToolbar($options['config_name']);
        $view->vars['environment'] = $this->kernel->getEnvironment();
    }

    public function getName(): string
    {
        return 'tiptap';
    }

    public function getBlockPrefix(): string
    {
        return 'tiptap';
    }

    private function getUploadUrl(): string
    {
        return $this->urlGenerator->generate('upload_file');
    }

    private function getToolbar(string $name): string
    {
        $toolbars = [
            'base' => ['bold', 'italic', 'underline', 'font-color', 'alignment', 'heading'],
            'full' => ['undo', 'redo',
                'bold', 'italic', 'strikethrough', 'underline',
                'alignment',
                'bullet-list', 'order-list',
                'table', 'image', 'image-size', 'link', 'block-quote', 'youtube',
                'fontfamily', 'fontsize', 'font-color', 'highlight',
                'heading',
            ],
        ];

        return json_encode($toolbars[$name] ?? $toolbars['base']);
    }
}
