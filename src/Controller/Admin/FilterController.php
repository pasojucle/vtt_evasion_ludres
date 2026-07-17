<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Filter\FilterAdvancedType;
use App\State\Filter\FilterProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/filter/', name: 'admin_fiter_')]
class FilterController extends AbstractController
{
    #[Route('advanced/{route}', name: 'advanced', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function filter(
        Request $request,
        FilterProvider $provider,
        string $route,
    ) {
        $filterConfig = $provider->getFilterConfig($route);
        if (!$filterConfig) {
            throw $this->createNotFoundException();
        }
        $dataClass = $filterConfig->getDataClass();
        $advancedFilter = $provider->getHydratedDto($request->query->all(), $dataClass);

        $form = $this->createForm(FilterAdvancedType::class, $advancedFilter, [
            'action' => $request->getPathInfo(),
            'fields' => $filterConfig->getFields(),
            'advanced_fields' => $filterConfig->getAdvancedFields(),
            'data_class' => $dataClass,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute($route, $advancedFilter->toArray());
        }

        return $this->render('components/_sheet.sheet.html.twig', [
            'view' => $provider->createSheet(),
            'form' => $form->createView(),
        ]);
    }
}
