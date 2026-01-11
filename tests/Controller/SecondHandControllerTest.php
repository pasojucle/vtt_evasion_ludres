<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Service\ProjectDirService;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;

class SecondHandControllerTest extends AbstractTestController
{
    public function testAdminSecondHand()
    {
        $this->testSecondHandList();
        // $this->testAddSecondHand();
        // $this->testValidateSecondHand();
        // $this->testContactSeller();
    }


    private function testSecondHandList(): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_MOVED_PERMANENTLY,'Home');


        // $identity = ['name' => 'Roue', 'firstName' => 'Libre', 'password' => 'test01'];

        // $this->client->request('GET', '/mon-compte/occasions');
        // $user = $this->getUserFromIdentity($identity);
        // $this->loginUser($user);
        // $this->assertResponseRedirects();
        // $this->client->followRedirect();
        // $this->assertResponseIsSuccessful();
        // $this->assertSelectorTextContains('a', '', 'Déposer une annonce');
        // $this->client->clickLink('Déposer une annonce');
    }

    // private function testAddSecondHand(): void
    // {
    //     $this->assertSelectorTextContains('.wrapper h1', 'Ajouter une annonce');
    //     $categoryRepository = static::getContainer()->get(CategoryRepository::class);
    //     $projectDir = static::getContainer()->get(ProjectDirService::class);
    //     $categories = $categoryRepository->findAll();
    //     $this->client->submitForm('Enregistrer', [
    //         'second_hand[isAgree]' => true,
    //         'second_hand[name]' => 'Test occasion',
    //         'second_hand[category]' => rand(1, count($categories) - 1),
    //         'second_hand[content]' => 'Ceci est un test pour ajouter une annonce d\'occasion',
    //         'second_hand[price]' => rand(0, 1500),
    //         'second_hand[filename]' => $projectDir->path('second_hands_directory_path', '11573645d15c198c06980-20763528-65749c55ee45b.jpg'),
    //     ]);
    //     $this->assertResponseRedirects();
    //     $this->client->followRedirect();
    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorTextContains('ul li div b', 'Test occasion');
    // }

    // private function testValidateSecondHand():void
    // {
    //     $this->logOut();
    //     $this->loginAdmin();
    //     $this->client->request('GET', '/admin/');
    //     $this->assertResponseRedirects();
    //     $this->client->followRedirect();
    //     $this->assertResponseIsSuccessful();
    //     $this->client->request('GET', '/admin/occasion/list');
    //     $this->assertSelectorExists('a[href="/admin/occasion/detail/1"]');
    //     $secondHand = $this->client->getCrawler()->filter('a[href="/admin/occasion/detail/1"]');
    //     $this->client->click($secondHand->link());
    //     $this->assertSelectorTextContains('a', '', 'Valider');
    //     $this->client->clickLink('Valider');
    // }

    // private function testContactSeller():void
    // {
    //     $this->client->request('GET', '/login');
    //     $this->loginUser();
    //     $this->client->request('GET', '/occasions');
    //     $this->assertSelectorExists('a[href="/occasion/detail/1"]');
    //     $secondHand = $this->client->getCrawler()->filter('a[href="/occasion/detail/1"]');
    //     $this->client->click($secondHand->link());
    //     $this->assertSelectorTextContains('a', '', 'Contacter le vendeur');
    //     $this->client->clickLink('Contacter le vendeur');
    // }
}