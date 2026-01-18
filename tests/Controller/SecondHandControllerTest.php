<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\ProjectDirService;
use App\Repository\CategoryRepository;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\HttpFoundation\Response;

class SecondHandControllerTest extends AbstractTestController
{
    public function testAdminSecondHand()
    {
        $name = sprintf('Test occasion %s', time());
        $user = $this->getUserFromIdentity(self::ADULT);

        $this->validateSecondHandList($user);
        $this->validateAddSecondHand($user, $name);
        $this->ValidateSecondHandByAdmin($name);
        $this->validateContactSeller($name);
    }

    private function validateSecondHandList(User $user): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_MOVED_PERMANENTLY,'Home');
        $this->loginUser($user);
        $this->client->request('GET', '/mon-compte/occasions');
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a', '', 'Déposer une annonce');
        $this->client->clickLink('Déposer une annonce');
    }

    private function validateAddSecondHand(User $user, string $name): void
    {
        $this->assertSelectorTextContains('.wrapper h1', 'Ajouter une annonce');
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categories = $categoryRepository->findAll();
        $form = $this->client->getCrawler()->selectButton('Enregistrer')->form();

        $values = $form->getPhpValues();
        $values['second_hand']['isAgree'] = 1;
        $values['second_hand']['name'] = $name;
        $values['second_hand']['category'] = $categories[rand(1, count($categories) - 1)]->getId();
        $values['second_hand']['content'] = 'Ceci est un test pour ajouter une annonce d\'occasion';
        $values['second_hand']['price'] = rand(0, 1500);

        $values['second_hand']['images'][0]['uploadFile'] = null;
        $projectDir = self::$kernel->getProjectDir();
        $sourcePath = $projectDir . '/tests/Assets/second_hand.jpg';
        if (!file_exists($sourcePath)) {
            throw new \Exception("L'image de test est introuvable dans tests/Assets/");
        }
        $tmpPath = $projectDir . '/var/tmp_bike_test.jpg';
        copy($sourcePath, $tmpPath);

        $files = $form->getPhpFiles();
        $files['second_hand']['images'][0]['uploadFile'] = [
            'tmp_name' => $tmpPath,
            'name' => 'test-image.jpg',
            'type' => 'image/jpeg',
            'size' => 123,
            'error' => 0,
        ];
        $this->client->request($form->getMethod(), $form->getUri(), $values, $files);

        $mainIdentity = $user->getMainIdentity();
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailAddressContains($email, 'Reply-To', $mainIdentity->getEmail());
        $this->assertEmailAddressContains($email, 'To', $this->getClubEmail());
        $this->assertEmailHeaderSame($email, 'Subject', 'Nouvelle Annonce d\'occasion sur le site VTT Evasion Ludres');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('ul li div b', 'Test occasion');
        $this->logOut();
    }

    private function ValidateSecondHandByAdmin(string $name):void
    {
        $this->loginAdmin();
        $this->client->request('GET', '/admin/');
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->client->request('GET', '/admin/occasion/list');
        $secondHand = $this->secondHandRepository->findOneBy(['name' => $name]);
        $selector = sprintf('a[href="/admin/occasion/detail/%s"]', $secondHand->getId());
        $this->assertSelectorExists($selector);
        $secondHandButton = $this->client->getCrawler()->filter($selector);
        $this->client->click($secondHandButton->link());
        $this->assertSelectorTextContains('a', '', 'Valider');
        $this->client->clickLink('Valider');
        $this->logOut();
    }

    private function validateContactSeller(string $name):void
    {
        $this->client->request('GET', '/login');
        $user = $this->getUserFromIdentity(self::SCHOOL_MEMBER);
        $this->loginUser($user);
        $this->client->request('GET', '/occasions');
        $secondHand = $this->secondHandRepository->findOneBy(['name' => $name]);
        $selector = sprintf('a[href="/occasion/detail/%s"]', $secondHand->getId());
        $this->assertSelectorExists($selector);
        $secondHandButton = $this->client->getCrawler()->filter($selector);
        $this->client->click($secondHandButton->link());
        $this->assertSelectorTextContains('a', '', 'Contacter le vendeur');
        $this->client->clickLink('Contacter le vendeur');

        $this->client->request('GET', $this->urlGenerator->generate('second_hand_message', ['secondHand' => $secondHand->getId()]));
        $this->client->submitForm('Contacter');

        $seller = $secondHand->getUser();
        $mainIdentity = $seller->getMainIdentity();
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailAddressContains($email, 'To', $mainIdentity->getEmail());
        $this->assertEmailHeaderSame($email, 'Subject', sprintf('Votre annonce %s', $name));
        $this->client->followRedirect();
    }
}