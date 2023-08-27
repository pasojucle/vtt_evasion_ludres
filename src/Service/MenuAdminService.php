<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuAdminService
{
    public function __construct(private RequestStack $request, private SeasonService $seasonService, private Security $security)
    {
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

    public function getMenuActived(): int
    {
        $menuActived = $this->request->getCurrentRequest()->cookies->get('admin_menu_actived');

        return (null !== $menuActived) ? (int) $menuActived : 0;
    }

    private function getManagementMenus(): array
    {
        return $this->getMenusGranted([
            [
                'label' => 'Programme',
                'route' => 'admin_bike_rides',
                'pattern' => '/^admin_bike_ride/',
                'role' => 'ROLE_REGISTER',
            ],
            [
                'label' => 'Adhérents',
                'route' => 'admin_users',
                'pattern' => '/^admin_user/',
                'role' => 'ROLE_REGISTER',
            ],
            [
                'label' => 'Inscriptions',
                'route' => 'admin_registrations',
                'pattern' => '/^admin_registration/',
                'role' => 'ROLE_REGISTER',
            ],
            [
                'label' => 'Assurances ' . $this->seasonService->getCurrentSeason(),
                'route' => 'admin_coverage_list',
                'pattern' => '/^admin_coverage/',
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
            [
                'label' => 'Sondages',
                'route' => 'admin_surveys',
                'pattern' => '/survey/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Pop up',
                'route' => 'admin_modal_window_list',
                'pattern' => '/popup/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Annonces d\'occasion',
                'route' => 'admin_second_hand_list',
                'pattern' => '/second_hand/',
                'role' => 'ROLE_ADMIN',
            ],
        ]);
    }

    private function getSettingMenu(): array
    {
        return $this->getMenusGranted([
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
                'label' => 'Images de fond',
                'route' => 'admin_background_list',
                'pattern' => '/admin_background/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Liens',
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
                'label' => 'Documentation',
                'route' => 'admin_documentation_list',
                'pattern' => '/documentation/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Roles du bureau et comité',
                'route' => 'admin_board_role_list',
                'pattern' => '/board_role/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Types de rando',
                'route' => 'admin_bike_ride_types',
                'pattern' => '/bike_ride_type/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Tarifs',
                'route' => 'admin_membership_fee',
                'pattern' => '/membership/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Indemnités',
                'route' => 'admin_indemnity_list',
                'pattern' => '/indemnity/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Paramètres',
                'route' => 'admin_groups_parameter',
                'pattern' => '/parameter/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Etapes des inscriptions',
                'route' => 'admin_registration_steps',
                'pattern' => '/registration_step/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Catégories d\'occadion',
                'route' => 'admin_category_list',
                'pattern' => '/categorie/',
                'role' => 'ROLE_ADMIN',
            ],
        ]);
    }

    private function getToolsMenu(): array
    {
        return $this->getMenusGranted([
            [
                'label' => 'Notification d\'erreur à l\'inscription',
                'route' => 'admin_registration_error',
                'pattern' => '/admin_registration_error/',
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
                'role' => 'ROLE_ADMIN',
            ],
        ]);
    }

    private function getMenusGranted(array $menus): array
    {
        $menusGranted = [];
        foreach ($menus as $menu) {
            if ($this->security->isGranted($menu['role'])) {
                $menusGranted[] = $menu;
            }
        }

        return $menusGranted;
    }
}
