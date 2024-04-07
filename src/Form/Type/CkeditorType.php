<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CkeditorType extends AbstractType
{
    protected ObjectManager $em;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'config_name',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['upload_url'] = $this->getUploadUrl();
        $view->vars['compound'] = false;
        $view->vars['enable'] = true;
        $view->vars['async'] = true;
        $view->vars['block_prefix'] = 'ckeditor';
        $view->vars['toolbar'] = $this->getToolbar($options['config_name']);
    }

    public function getName(): string
    {
        return 'ckeditor';
    }

    public function getBlockPrefix(): string
    {
        return 'ckeditor';
    }

    private function getUploadUrl(): string
    {
        return $this->urlGenerator->generate('upload_file', ['directory' => 'data_upload']);
    }

    private function getToolbar(string $name): array
    {
        $toolbars = [
            'base' => [ 'bold', 'italic', 'underline', '|', 'fontColor', '|', 'alignment', '|', 'heading'],
            'full' => [
                'undo', 'redo',
                '|', 'bold', 'italic', 'strikethrough', 'underline',
                '|', 'alignment',
                '|', 'bulletedList', 'numberedList',
                '|', 'insertTable', 'imageUpload', 'resizeImage', 'mediaEmbed', 'link',
                '|', 'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
                '|', 'heading',
            ],
        ];

        return $toolbars[$name];
    }
}
