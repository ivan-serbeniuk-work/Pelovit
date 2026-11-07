<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\TeamMember;

use App\Models\TeamMember;
use App\MoonShine\Resources\TeamMember\Pages\TeamMemberDetailPage;
use App\MoonShine\Resources\TeamMember\Pages\TeamMemberFormPage;
use App\MoonShine\Resources\TeamMember\Pages\TeamMemberIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<TeamMember, TeamMemberIndexPage, TeamMemberFormPage, TeamMemberDetailPage>
 */
class TeamMemberResource extends ModelResource
{
    protected string $model = TeamMember::class;

    protected string $title = 'Команда';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            TeamMemberIndexPage::class,
            TeamMemberFormPage::class,
            TeamMemberDetailPage::class,
        ];
    }
}
