<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'admin_home', methods: ['GET'])]
    public function adminHome(): Response
    {
        if ($this->isGranted('USER_LIST') && !$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_users');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }
        return $this->redirectToRoute('admin_bike_rides');
    }
}
