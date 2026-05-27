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

class MakeDeletePattern extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:delete-pattern';
    }

    public static function getCommandDescription(): string
    {
        return 'Génère le pattern complet pour une route delete ( Provider, Processor).';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('entity', InputArgument::REQUIRED, 'Le nom de l\'entité cible (ex: Skill)')
            ->addArgument('message', InputArgument::REQUIRED, 'Le message de confirmation (ex: Etes vous certain de supprimer le role %s)')
            ->addArgument('getter', InputArgument::REQUIRED, 'La fonction de l\'entité cible pour personnaliser le message (ex: getName())')
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entity = ucfirst($input->getArgument('entity'));
        $message = $input->getArgument('message');
        $getter = $input->getArgument('getter');

        $generator->generateClass(
            'App\\State\\' . $entity . '\\Provider\\' . $entity . 'DeleteProvider',
            dirname(__DIR__) . '/Resources/skeleton/DeleteProvider.tpl.php',
            [
                'entity_name' => $entity,
                'message' => $message,
                'getter' => $getter,
            ]
        );

        $generator->generateClass(
            'App\\State\\' . $entity . '\\Processor\\' . $entity . 'DeleteProcessor',
            dirname(__DIR__) . '/Resources/skeleton/DeleteProcessor.tpl.php',
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