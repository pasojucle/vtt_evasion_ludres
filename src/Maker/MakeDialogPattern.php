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

class MakeDialogPattern extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:dialog-pattern';
    }

    public static function getCommandDescription(): string
    {
        return 'Génère le pattern complet pour une route avec confirmation avec une modale ( Provider, Processor).';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('entity', InputArgument::REQUIRED, 'Le nom de l\'entité cible (ex: Skill)')
            ->addArgument('action', InputArgument::REQUIRED, 'Le nom de l\'action (ex: Delete)')
            ->addArgument('message', InputArgument::REQUIRED, 'Le message de affiché sur la modale (ex: Etes vous certain de supprimer le role %s)')
            ->addArgument('getter', InputArgument::REQUIRED, 'La fonction de l\'entité cible pour personnaliser le message (ex: getName())')
            ->addArgument('$route', InputArgument::REQUIRED, 'Le nom de la route pour retourner sur la liste (ex: admin_user_list)')
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entity = ucfirst($input->getArgument('entity'));
        $action = ucfirst($input->getArgument('action'));
        $message = $input->getArgument('message');
        $getter = $input->getArgument('getter');
        $route = $input->getArgument('route');

        $generator->generateClass(
            'App\\State\\' . $entity . '\\Provider\\' . $entity . $action .'Provider',
            dirname(__DIR__) . '/Resources/skeleton/DialogProvider.tpl.php',
            [
                'entity_name' => $entity,
                'action_name' => $action,
                'message' => $message,
                'getter' => $getter,
            ]
        );

        $generator->generateClass(
            'App\\State\\' . $entity . '\\Processor\\' . $entity . $action .'Processor',
            dirname(__DIR__) . '/Resources/skeleton/DialogProcessor.tpl.php',
            [
                'entity_name' => $entity,
                'action_name' => $action,
                'route' => $route,
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