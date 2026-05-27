<?php

declare(strict_types=1);
 
namespace App\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeListPattern extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:list-pattern';
    }

    public static function getCommandDescription(): string
    {
        return 'Génère le pattern complet pour une route de liste (DTO, Config, Provider, Mapper).';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('entity', InputArgument::REQUIRED, 'Le nom de l\'entité cible (ex: Skill)')
            ->addArgument('route', InputArgument::REQUIRED, 'Le nom de la route associée (ex: admin_skill_list)')
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entity = ucfirst($input->getArgument('entity'));
        $route = $input->getArgument('route');

        $generator->generateClass(
            'App\\Dto\\Filter\\' . $entity . 'Filter',
            'templates/bundles/MakerBundle/skeleton/Filter.tpl.php',
            [
                'entity_name' => $entity,
            ]
        );

        $generator->generateClass(
            'App\\Service\\Filter\\' . $entity . 'FilterConfig',
            'templates/bundles/MakerBundle/skeleton/FilterConfig.tpl.php',
            [
                'entity_name' => $entity,
                'route' => $route,
            ]
        );

        $generator->generateClass(
            'App\\State\\' . $entity . '\\Provider\\' . $entity . 'ListProvider',
            'templates/bundles/MakerBundle/skeleton/ListProvider.tpl.php',
            [
                'entity_name' => $entity,
            ]
        );

        $generator->generateClass(
            'App\\Mapper\\' . $entity . '\\' . $entity . 'ListMapper',
            'templates/bundles/MakerBundle/skeleton/ListMapper.tpl.php',
            [
                'entity_name' => $entity,
                'route' => $route,
            ]
        );

        $generator->generateFile(
            'templates/' . strtolower($entity) . '/admin/_frame_list.html.twig',
            'templates/bundles/MakerBundle/skeleton/_frame_list.tpl.php',
            [
                'entity_name' => $entity,
            ]
        );
        
        $generator->writeChanges();
        $this->writeSuccessMessage($io);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // Pas de dépendances spécifiques requises pour ce pattern
    }
}