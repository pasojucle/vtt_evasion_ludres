<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use DOMDocument;
use App\Entity\User;
use App\Repository\BikeRideRepository;
use App\Service\SeasonService;
use App\Repository\UserRepository;
use App\Repository\LevelRepository;
use App\Repository\LicenceRepository;
use App\Repository\SessionRepository;
use App\Repository\IdentityRepository;
use Symfony\Component\DomCrawler\Form;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\TextareaFormField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractTestController extends WebTestCase
{
    public KernelBrowser $client;
    public UserRepository $userRepository;
    public IdentityRepository $identityRepository;
    public SessionRepository $sessionRepository;
    public LicenceRepository $licenceRepository;
    public UrlGeneratorInterface $urlGenerator;
    public SeasonService $seasonService;
    public LevelRepository $levelRepository;
    public BikeRideRepository $bikeRideRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = static::createClient([], ['REMOTE_ADDR' => '11.11.11.11']);
        $this->client->disableReboot();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->identityRepository = static::getContainer()->get(IdentityRepository::class);
        $this->sessionRepository = static::getContainer()->get(SessionRepository::class);
        $this->licenceRepository = static::getContainer()->get(LicenceRepository::class);
        $this->urlGenerator = static::getContainer()->get(UrlGeneratorInterface::class);
        $this->seasonService = static::getContainer()->get(SeasonService::class);
        $this->levelRepository = static::getContainer()->get(LevelRepository::class);
        $this->bikeRideRepository = static::getContainer()->get(BikeRideRepository::class);
    }
    
    public function addAutocompleteField(Form &$form, string $name): void
    {
        $domdocument = new DOMDocument;
        $ff = $domdocument->createElement('textarea');
        $ff->setAttribute('name', $name);
        $formfield = new TextareaFormField($ff);

        $form->set($formfield); 
    }

    public function loginUser(User $user): void
    {
        $this->client->getCookieJar()->clear();
        $this->client->request('GET', '/login');
        $this->client->loginUser($user);
    }

    public function logOut():void
    {
        $this->client->request('GET', '/logout');
        $this->client->getCookieJar()->clear();
        $this->getEntityManager()->clear();
        $this->client->followRedirect();
    }

    protected function getEntityManager(): \Doctrine\ORM\EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }
}