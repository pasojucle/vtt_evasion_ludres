<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class MenuAdminService
{
    private RequestStack $request;
    public function __construct(RequestStack $request)
    {
        $this->request = $request;
    }
    public function getMenuGroupsAdmin(): array
    {
        return [
            [
                'label' => 'Gestion',
                'route' => null,
                'pattern' => null,
                'subMenus' => $this->getManagementMenus(),
                'role' => 'ROLE_FRAME',
            ],
            [
                'label' => 'Paramètrage',
                'route' => null,
                'pattern' => null,
                'subMenus' => $this->getSettingMenu(),
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Outils',
                'route' => null,
                'pattern' => null,
                'subMenus' => $this->getToolsMenu(),
                'role' => 'ROLE_ADMIN',
            ],
        ];
    }

    private function getManagementMenus(): array
    {
        return [
            [
                'label' => 'Programme',
                'route' => 'admin_events',
                'pattern' => '/event/',
                'role' => 'ROLE_FRAME',
            ],
            [
                'label' => 'Adhérents',
                'route' => 'admin_users',
                'pattern' => '/user/',
                'role' => 'ROLE_REGISTER',
            ],
            [
                'label' => 'Inscriptions',
                'route' => 'admin_registrations',
                'pattern' => '/admin_registrations/',
                'role' => 'ROLE_REGISTER',
            ],
            [
                'label' => 'Boutique',
                 'route' => 'admin_products',
                 'pattern' => '/product/',
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Commandes',
                 'route' => 'admin_orders',
                 'pattern' => '/order/',
                 'role' => 'ROLE_ADMIN',
            ],
        ];
    }
    private function getSettingMenu(): array
    {
        return [
            [
                'label' => 'Page d\'accueil',
                 'route' => 'admin_home_contents',
                 'pattern' => '/admin_home/',
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Contenu des pages',
                 'route' => 'admin_contents',
                 'pattern' => '/admin_content/',
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Gestion des liens',
                 'route' => 'admin_links',
                 'pattern' => '/link/',
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Niveaux',
                 'route' => 'admin_levels',
                 'pattern' => '/level/',
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Paramètrage des inscriptions',
                 'route' => 'admin_registration_steps',
                 'pattern' => '/registration_step/',
                 'role' => 'ROLE_ADMIN',
            ],
        ];
    }
    private function getToolsMenu(): array
    {
        return [
            [
                'label' => 'Notification d\'erreur à l\'inscription',
                 'route' => 'admin_registration_error',
                 'pattern' => '/admin_registration_error/',
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Attestation d\'inscription pour CE',
                 'route' => 'admin_registration_certificate',
                 'pattern' => '/admin_registration_certificate/',
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Journal des erreurs',
                 'route' => 'admin_log_errors',
                 'pattern' => '/admin_log_errors/',
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Supprimer un adhérent',
                 'route' => 'admin_tool_delete_user',
                 'pattern' => '/produit/',
                 'role' => 'ROLE_SUPER_ADMIN',
            ],
        ];
    }

    public function getMenuActived(): int
    {
        $menuActived =  $this->request->getCurrentRequest()->cookies->get('admin_menu_actived');

        return (null !== $menuActived) ? (int) $menuActived : 0;
    }
}