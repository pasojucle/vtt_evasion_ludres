<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\FilterAdvancedType;
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
        $form = $this->createForm(FilterAdvancedType::class, $provider->getHydratedDto($request->query->all(), $dataClass), [
            'action' => $request->getPathInfo(),
            'fields' => $filterConfig->getFields(),
            'advanced_fields' => $filterConfig->getAdvancedFields(),
            'data_class' => $dataClass,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $advancedFilter = $form->getData();
            
            return $this->redirectToRoute($route, $advancedFilter->toArray());
        }

        return $this->render('filter/admin/advanced_filter.sheet.html.twig', [
            'sheet' => $provider->createSheet(),
            'form' => $form->createView(),
        ]);
    }
}
