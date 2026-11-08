<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product\Pages;

use App\MoonShine\Concerns\ValidatesUploadedImages;
use App\MoonShine\Concerns\ValidatesUploadedVideos;
use App\MoonShine\Resources\Category\CategoryResource;
use App\MoonShine\Resources\Product\ProductResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\ListOf;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Throwable;

/**
 * @extends FormPage<ProductResource>
 */
class ProductFormPage extends FormPage
{
    use ValidatesUploadedImages;
    use ValidatesUploadedVideos;

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            FlexibleRender::make('<script>
(function(){
    const T={а:"a",б:"b",в:"v",г:"h",ґ:"g",д:"d",е:"e",є:"ie",ж:"zh",з:"z",и:"y",і:"i",ї:"i",й:"i",к:"k",л:"l",м:"m",н:"n",о:"o",п:"p",р:"r",с:"s",т:"t",у:"u",ф:"f",х:"kh",ц:"ts",ч:"ch",ш:"sh",щ:"shch",ю:"iu",я:"ia",ь:"",ъ:""};
    function toSlug(s){
        return s.split("").map(c=>T[c.toLowerCase()]??(c.match(/[a-z0-9]/)?c.toLowerCase():c)).join("")
            .replace(/[^a-z0-9]+/g,"-").replace(/^-+|-+$/g,"");
    }
    function bindSlug(){
        const n=document.querySelector(\'input[name="name"]\');
        const sl=document.querySelector(\'input[name="slug"]\');
        if(!n||!sl||n._slugBound)return;
        n._slugBound=true;
        n.addEventListener("input",function(){
            sl.value=toSlug(n.value);
            sl.dispatchEvent(new Event("input",{bubbles:true}));
        });
    }
    document.addEventListener("DOMContentLoaded",bindSlug);
    document.addEventListener("moonshine:page-changed",bindSlug);
    setTimeout(bindSlug,300);
})();
</script>'),
            Box::make('Основне', [
                Grid::make([
                    Column::make([
                        Text::make('Назва', 'name')->required(),
                        Text::make('Slug', 'slug')->hint('Заповниться автоматично від назви'),
                        BelongsTo::make('Категорія', 'category', fn ($item) => $item->name, resource: CategoryResource::class)->required(),
                        Text::make('Бренд', 'brand'),
                        TinyMce::make('Опис', 'description'),
                        TinyMce::make('Інструкція', 'instruction')
                            ->hint('Повний текст: склад, активи, спосіб застосування. Показується окремим блоком на сторінці товару'),
                    ])->columnSpan(8),
                    Column::make([
                        Number::make('Ціна (₴)', 'price')->required()->step(0.01),
                        Number::make('Стара ціна (₴)', 'old_price')->step(0.01)->nullable(),
                        Switcher::make('Активний', 'is_active'),
                        Switcher::make('Хіт продажів', 'is_featured'),
                    ])->columnSpan(4),
                ]),
            ]),
            Box::make('SEO', [
                Grid::make([
                    Column::make([
                        Text::make('Meta Title', 'meta_title')
                            ->hint('Рекомендована довжина: 50–60 символів. Якщо порожньо — береться назва товару.'),
                        Textarea::make('Meta Description', 'meta_description')
                            ->hint('Рекомендована довжина: 120–160 символів.'),
                        Text::make('Meta Keywords', 'meta_keywords')
                            ->hint('Ключові слова через кому. Більшість пошукових систем ігнорують.'),
                    ])->columnSpan(6),
                    Column::make([
                        Text::make('OG Title', 'og_title')
                            ->hint('Заголовок для соціальних мереж. Якщо порожньо — береться Meta Title.'),
                        Textarea::make('OG Description', 'og_description')
                            ->hint('Опис для соціальних мереж. Якщо порожньо — береться Meta Description.'),
                        Switcher::make('Заборонити індексацію (noindex)', 'no_index')
                            ->hint('Увімкніть щоб приховати сторінку з пошукових систем.'),
                    ])->columnSpan(6),
                ]),
            ]),
            Box::make('Зображення та відео', [
                Image::make('Головне фото', 'image')
                    ->dir('products')
                    ->disk('public_root'),
                Image::make('Галерея', 'images')
                    ->dir('products')
                    ->disk('public_root')
                    ->multiple(),
                Text::make('Відео (YouTube)', 'video_url')
                    ->hint('Посилання на ролик з YouTube, напр. https://www.youtube.com/watch?v=... або https://youtu.be/... Показується на сторінці товару.'),
                // Залиті ролики стають слайдами галереї поряд з фото.
                // Постер необов'язковий: без нього браузер сам показує
                // перший кадр, бо плеєр вантажиться з preload="metadata".
                Json::make('Відеофайли', 'videos')
                    ->fields([
                        // removable, на відміну від полів із картинками:
                        // ролик на 50 МБ не можна лишати на диску назавжди
                        // тільки тому, що його нема чим прибрати.
                        File::make('Відео', 'file')
                            ->dir('videos')
                            ->disk('public_root')
                            ->allowedExtensions(['mp4', 'webm'])
                            ->removable(),
                        Image::make('Постер', 'poster')
                            ->dir('images/videos')
                            ->disk('public_root'),
                        Text::make('Підпис', 'title'),
                    ])
                    ->creatable()
                    ->removable()
                    ->hint('MP4 або WebM, до 50 МБ на файл.'),
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
            'video_url' => ['nullable', 'url', 'regex:~(youtube(-nocookie)?\.com|youtu\.be)/~i'],
            'image' => $this->imageRules(),
            'images.*' => $this->imageRules(),
            'videos.*.file' => $this->videoRules(),
            'videos.*.poster' => $this->imageRules(),
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
