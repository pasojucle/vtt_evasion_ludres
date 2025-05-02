<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class FilterController extends AbstractController
{
    #[Route('/admin/save/list/filtered', name: 'admin_save_list_filtered', methods:['POST'], options:['expose' => true])]
    public function saveFilter(Request $request): JsonResponse
    {
        $list = $request->request->all('list');
        dump($list);
        if (array_key_exists('name', $list) && array_key_exists('values', $list)) {
            $session = $request->getSession();
            $session->set($list['name'], $list['values']);
            dump($session->get('admin_users_list'));
        }
        
        return new JsonResponse(['codeError => 0']);
    }
}
