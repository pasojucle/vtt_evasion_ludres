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
                'role' => ['BIKE_RIDE_LIST', 'USER_LIST', 'SURVEY_LIST', 'SECOND_HAND_LIST', 'MODAL_WINDOW_LIST', 'PRODUCT_LIST'],
            ],
            [
                'label' => 'Contenus',
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
                'label' => 'Tableau de bord',
                'route' => 'admin_dashboard',
                'pattern' => '/^admin_dashboard/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Programme',
                'route' => 'admin_bike_rides',
                'pattern' => '/^admin_bike_ride/',
                'role' => 'BIKE_RIDE_LIST',
            ],
            [
                'label' => 'Adhérents',
                'route' => 'admin_users',
                'pattern' => '/^admin_user/',
                'role' => 'USER_LIST',
            ],
            [
                'label' => 'Participation',
                'route' => 'admin_participation_list',
                'pattern' => '/^admin_participation/',
                'role' => 'PARTICIPATION_VIEW',
            ],
            [
                'label' => 'Inscriptions',
                'route' => 'admin_registrations',
                'pattern' => '/^admin_registration/',
                'role' => 'USER_LIST',
            ],
            [
                'label' => 'Assurances ' . $this->seasonService->getCurrentSeason(),
                'route' => 'admin_coverage_list',
                'pattern' => '/^admin_coverage/',
                'role' => 'USER_LIST',
            ],
            [
                'label' => 'Boutique',
                'route' => 'admin_products',
                'pattern' => '/product/',
                'role' => 'PRODUCT_LIST',
            ],
            [
                'label' => 'Commandes',
                'route' => 'admin_orders',
                'pattern' => '/order/',
                'role' => 'PRODUCT_LIST',
            ],
            [
                'label' => 'Sondages',
                'route' => 'admin_surveys',
                'pattern' => '/survey/',
                'role' => 'SURVEY_LIST',
            ],
            [
                'label' => 'Pop up',
                'route' => 'admin_notification_list',
                'pattern' => '/modal/',
                'role' => 'MODAL_WINDOW_LIST',
            ],
            [
                'label' => 'Annonces d\'occasion',
                'route' => 'admin_second_hand_list',
                'pattern' => '/second_hand/',
                'role' => 'SECOND_HAND_LIST',
            ],
            [
                'label' => 'Diaporama',
                'route' => 'admin_slideshow_list',
                'pattern' => '/slideshow/',
                'role' => 'SLIDESHOW_LIST',
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
                'label' => 'Documentation',
                'route' => 'admin_documentation_list',
                'pattern' => '/documentation/',
                'role' => 'DOCUMENTATION_LIST',
            ],
            [
                'label' => 'Tarifs',
                'route' => 'admin_membership_fee',
                'pattern' => '/membership/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Messages',
                'route' => 'admin_message_list',
                'pattern' => '/message/',
                'role' => 'ROLE_SUPER_ADMIN',
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
                'label' => 'Maintenance',
                'route' => 'admin_service',
                'pattern' => '/parameter/',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Supprimer un adhérent',
                'route' => 'admin_tool_delete_user',
                'pattern' => '/tool/',
                'role' => 'ROLE_ADMIN',
            ],
        ]);
    }

    public function getMenusGranted(array $menus): array
    {
        $menusGranted = [];
        foreach ($menus as $menu) {
            if ($this->isGranted($menu['role'])) {
                $menusGranted[] = $menu;
            }
        }

        return $menusGranted;
    }

    private function isGranted(string| array $role): bool
    {
        if (!is_array($role)) {
            return $this->security->isGranted($role);
        }

        foreach ($role as $item) {
            if ($this->security->isGranted($item)) {
                return true;
            }
        }
        return false;
    }
}
