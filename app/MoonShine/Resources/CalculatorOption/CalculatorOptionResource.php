<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalculatorOption;

use App\Models\CalculatorOption;
use App\MoonShine\Concerns\OptimizesUploadedImages;
use App\MoonShine\Resources\CalculatorOption\Pages\CalculatorOptionDetailPage;
use App\MoonShine\Resources\CalculatorOption\Pages\CalculatorOptionFormPage;
use App\MoonShine\Resources\CalculatorOption\Pages\CalculatorOptionIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<CalculatorOption, CalculatorOptionIndexPage, CalculatorOptionFormPage, CalculatorOptionDetailPage>
 */
class CalculatorOptionResource extends ModelResource
{
    use OptimizesUploadedImages;

    /** @var list<string> Колонки з картинками — великі фото стискаються після збереження. */
    protected array $imageColumns = ['image'];

    protected string $model = CalculatorOption::class;

    protected string $title = 'Калькулятор: опції';

    protected string $column = 'name';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            CalculatorOptionIndexPage::class,
            CalculatorOptionFormPage::class,
            CalculatorOptionDetailPage::class,
        ];
    }
}
