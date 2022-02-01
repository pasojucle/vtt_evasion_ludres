<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\MenuService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class RobotsFileCommand extends Command
{
    protected static $defaultName = 'make:robots:file';

    private RequestContext $requestContext;

    private ParameterBagInterface $parameterBag;

    private MenuService $navService;

    private UrlGeneratorInterface $urlGenerator;

    private string $publicDir;

    public function __construct(
        RequestContext $requestContext,
        ParameterBagInterface $parameterBag,
        MenuService $navService,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();
        $this->requestContext = $requestContext;
        $this->parameterBag = $parameterBag;
        $this->navService = $navService;
        $this->urlGenerator = $urlGenerator;
        $this->publicDir = $this->parameterBag->get('public_directory');
    }

    protected function configure()
    {
        $this
            ->setDescription('Make robots file')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $content = "User-agent : *\n"
                . "\n"
                . 'Sitemap : ' . $this->requestContext->getScheme() . '://' . $this->requestContext->getHost() . '/sitemap.xml'
                ;
        // Écrit le résultat dans le fichier
        file_put_contents($this->publicDir . DIRECTORY_SEPARATOR . 'robots.txt', $content);

        $this->makeSiteMape();

        $io->success('Robots file is created');

        return 0;
    }

    private function makeSiteMape()
    {
        $mainNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($this->navService->getIndexableRoutes() as $route) {
            $rN = $mainNode->addChild('url');
            $rN->addChild('loc', $this->urlGenerator->generate($route, [], UrlGeneratorInterface::ABSOLUTE_URL));
        }
        $mainNode->asXML($this->publicDir . DIRECTORY_SEPARATOR . 'sitemap.xml');
    }
}
