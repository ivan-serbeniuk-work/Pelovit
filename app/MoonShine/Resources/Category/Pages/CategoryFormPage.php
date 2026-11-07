<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Category\Pages;

use App\MoonShine\Concerns\ValidatesUploadedImages;
use App\MoonShine\Resources\Category\CategoryResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends FormPage<CategoryResource>
 */
class CategoryFormPage extends FormPage
{
    use ValidatesUploadedImages;

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            FlexibleRender::make('<script>
(function(){
    const T={а:"a",б:"b",в:"v",г:"h",ґ:"g",д:"d",е:"e",є:"ie",ж:"zh",з:"z",и:"y",і:"i",ї:"i",й:"i",к:"k",л:"l",м:"m",н:"n",о:"o",п:"p",р:"r",с:"s",т:"t",у:"u",ф:"f",х:"kh",ц:"ts",ч:"ch",ш:"sh",щ:"shch",ю:"iu",я:"ia",ь:"",ъ:""};
    function toSlug(s){return s.split("").map(c=>T[c.toLowerCase()]??(c.match(/[a-z0-9]/)?c.toLowerCase():c)).join("").replace(/[^a-z0-9]+/g,"-").replace(/^-+|-+$/g,"");}
    function bindSlug(){
        const n=document.querySelector(\'input[name="name"]\');
        const sl=document.querySelector(\'input[name="slug"]\');
        if(!n||!sl||n._slugBound)return;
        n._slugBound=true;
        n.addEventListener("input",function(){sl.value=toSlug(n.value);sl.dispatchEvent(new Event("input",{bubbles:true}));});
    }
    document.addEventListener("DOMContentLoaded",bindSlug);
    setTimeout(bindSlug,300);
})();
</script>'),
            Box::make([
                Text::make('Назва', 'name')->required(),
                Text::make('Slug', 'slug')->hint('Заповниться автоматично якщо порожньо'),
                BelongsTo::make('Батьківська категорія', 'parent', fn ($item) => $item->name, resource: CategoryResource::class)
                    ->nullable()
                    ->valuesQuery(function ($query) {
                        $currentId = $this->getResource()->getItem()?->getKey();
                        if ($currentId) {
                            $query->where('id', '!=', $currentId);
                        }

                        return $query;
                    }),
                Number::make('Порядок сортування', 'sort_order'),
                // Картинки категорій лежать у public/images, а не в public/categories.
                Image::make('Зображення', 'image')->dir('images')->disk('public_root'),
                Switcher::make('Активна', 'is_active'),
            ]),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons();
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'image' => $this->imageRules(),
        ];
    }

    /**
     * @param  FormBuilder  $component
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
