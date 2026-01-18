<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use DOMDocument;
use App\Entity\User;
use App\Entity\BikeRideType;
use App\Service\SeasonService;
use App\Repository\UserRepository;
use App\Repository\LevelRepository;
use App\Repository\ClusterRepository;
use App\Repository\LicenceRepository;
use App\Repository\SessionRepository;
use App\Repository\BikeRideRepository;
use App\Repository\IdentityRepository;
use Symfony\Component\DomCrawler\Form;
use App\Repository\SecondHandRepository;
use App\DataFixtures\Common\UserFixtures;
use App\DataFixtures\Common\BikeRideTypeFixtures;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Field\TextareaFormField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractTestController extends WebTestCase
{
    public const SCHOOL_MEMBER = ['name' => 'Frein', 'firstName' => 'Hydraulique', 'password' => 'test01'];
    PUBLIC CONST ADULT = ['name' => 'Roue', 'firstName' => 'Libre', 'password' => 'test01'];

    public KernelBrowser $client;
    public UserRepository $userRepository;
    public IdentityRepository $identityRepository;
    public SessionRepository $sessionRepository;
    public LicenceRepository $licenceRepository;
    public UrlGeneratorInterface $urlGenerator;
    public SeasonService $seasonService;
    public LevelRepository $levelRepository;
    public BikeRideRepository $bikeRideRepository;
    public ClusterRepository $clusterRepository;
    public SecondHandRepository $secondHandRepository;

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
        $this->clusterRepository = static::getContainer()->get(ClusterRepository::class);
        $this->secondHandRepository = static::getContainer()->get(SecondHandRepository::class);
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

    public function getUserFromIdentity(array $identity): User
    {
        $userIdentity = $this->identityRepository->findOneBy(['name' => $identity['name'], 'firstName' => $identity['firstName']]);
        $this->assertNotNull($userIdentity, sprintf('Aucun utilisateur trouvé pour %s %s', $identity['name'], $identity['firstName']));

        return $userIdentity->getUser();        
    }

    public function loginAdmin(): void
    {
        $licenceNumber = UserFixtures::getLicenceNumberFromReference(UserFixtures::USER_ADMIN);
        $admin = $this->userRepository->findOneByLicenceNumber($licenceNumber);
        $this->assertNotNull($admin, sprintf('Aucun admin trouvé pour le numéro de licence %s', $licenceNumber));

        $this->loginUser($admin);
    }

    public function getBikeRideTypeFromReference(string $reference): object
    {
        return $this->client->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(BikeRideType::class)
            ->findOneBy(['name' => BikeRideTypeFixtures::getBikeRideTypeNameFromReference($reference)]);
    }

    public function getClubEmail(): string
    {
        $parameterBag = static::getContainer()->get(ParameterBagInterface::class);
        return $parameterBag->get('club_email');
    }
}