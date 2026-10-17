<?php

declare(strict_types=1);

namespace App\Mapper\Survey;

use App\Dto\View\SheetFormWrapperView;
use App\Dto\View\SheetTabsView;
use App\Dto\View\Survey\Tab\IssuesView;
use App\Dto\View\Survey\Tab\MainView;
use App\Dto\View\Survey\Tab\ParticipantsView;
use App\Dto\View\TabView;
use App\Entity\Survey;

class SurveyUpdateMapper
{
    public function mapToView(Survey $data, string $action, int $currentTab): SheetFormWrapperView
    {
        return new SheetFormWrapperView(
            title: sprintf('%s un sondage', $action),
            description: sprintf('%s un sondage en définissant les paramètres généreaux, l\'image, les tailles...', $action),
            action: $action,
            frameId: sprintf('product-update-%s', $data->getId() ?? ''),
            formView: new SheetTabsView([
                new TabView(
                    title: 'Général',
                    index: 1,
                    isActive: 1 === $currentTab,
                    icon: 'lucide:info',
                    view: new MainView(),
                ),
                new TabView(
                    title: 'Participants',
                    index: 2,
                    isActive: 2 === $currentTab,
                    icon: 'lucide:users',
                    view: new ParticipantsView(),
                ),
                new TabView(
                    title: 'Questions',
                    index: 3,
                    isActive: 3 === $currentTab,
                    icon: 'lucide:circle-question-mark',
                    view: new IssuesView(),
                ),
            ]),
        );
    }
}
