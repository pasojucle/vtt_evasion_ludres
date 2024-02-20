<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Service\ProjectDirService;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecondHandControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    public function testAdminList()
    {
        $this->client = static::createClient([], ['REMOTE_ADDR' => '11.11.11.11']);
        $this->cleanDataBase();
        $this->testSecondHandList();
        $this->testAddSecondHand();
        $this->testValidateSecondHand();
        $this->testContactSeller();
    }

    private function cleanDataBase():void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $connection = $entityManager->getConnection();
        $connection->executeQuery("SET FOREIGN_KEY_CHECKS=0;");
        foreach(['second_hand'] as $table) {
            $query = sprintf("TRUNCATE TABLE `%s`", $table);
            $connection->executeQuery($query);
        }
        $connection->executeQuery("SET FOREIGN_KEY_CHECKS=1;");
    }

    private function loginUser(): void
    {
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $users = $this->userRepository->findAllMemberByCurrentSeason();
        $user = $users[rand(0, count($users) - 1)];
        $this->client->loginUser($user);
    }

    private function loginAdmin(): void
    {
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $testAdmin = $this->userRepository->findOneByLicenceNumber('624758');
        $this->client->loginUser($testAdmin);
    }


    private function testSecondHandList(): void
    {
        $this->client->request('GET', '/mon-compte/occasions');
        $this->loginUser();
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a', '', 'Déposer une annonce');
        $this->client->clickLink('Déposer une annonce');
    }

    private function testAddSecondHand(): void
    {
        $this->assertSelectorTextContains('.wrapper h1', 'Ajouter une annonce');
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $projectDir = static::getContainer()->get(ProjectDirService::class);
        $categories = $categoryRepository->findAll();
        $this->client->submitForm('Enregistrer', [
            'second_hand[isAgree]' => true,
            'second_hand[name]' => 'Test occasion',
            'second_hand[category]' => rand(1, count($categories) - 1),
            'second_hand[content]' => 'Ceci est un test pour ajouter une annonce d\'occasion',
            'second_hand[price]' => rand(0, 1500),
            'second_hand[filename]' => $projectDir->path('second_hands_directory_path', '11573645d15c198c06980-20763528-65749c55ee45b.jpg'),
        ]);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('ul li div b', 'Test occasion');
    }

    private function testValidateSecondHand():void
    {
        $this->client->request('GET', '/logout');
        $this->client->request('GET', '/admin/');
        $this->loginAdmin();
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->client->request('GET', '/admin/occasion/list');
        $this->assertSelectorExists('a[href="/admin/occasion/detail/1"]');
        $secondHand = $this->client->getCrawler()->filter('a[href="/admin/occasion/detail/1"]');
        $this->client->click($secondHand->link());
        $this->assertSelectorTextContains('a', '', 'Valider');
        $this->client->clickLink('Valider');
    }

    private function testContactSeller():void
    {
        $this->client->request('GET', '/logout');
        $this->client->request('GET', '/login');
        $this->loginUser();
        $this->client->request('GET', '/occasions');
        $this->assertSelectorExists('a[href="/occasion/detail/1"]');
        $secondHand = $this->client->getCrawler()->filter('a[href="/occasion/detail/1"]');
        $this->client->click($secondHand->link());
        $this->assertSelectorTextContains('a', '', 'Contacter le vendeur');
        $this->client->clickLink('Contacter le vendeur');
    }
}