<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'admin_home', methods: ['GET'])]
    public function adminHome(): Response
    {
        if ($this->isGranted('USER_NAV') && !$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_users');
        }
        return $this->redirectToRoute('admin_bike_rides');
    }
}
