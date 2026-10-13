<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\SurveyIssueAutocompleteFilter;
use App\State\SurveyIssue\Provider\SurveyIssueAutocompleteProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/issue/survey', name: 'admin_survey_issue_')]
class SurveyIssueController extends AbstractController
{
    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET'])]
    #[IsGranted('SURVEY_LIST')]
    public function memberAutocomplete(
        SurveyIssueAutocompleteProvider $provider,
        Request $request
    ): JsonResponse {
        /**  @var SurveyIssueAutocompleteFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), SurveyIssueAutocompleteFilter::class);
        $query = $request->query->get('query');

        return new JsonResponse(['results' => $provider->getAutocompleteChoices($query, $filter)]);
    }
}
