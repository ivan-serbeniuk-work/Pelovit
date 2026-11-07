<?php

namespace Tests\Feature\Admin;

use App\Models\AboutFounder;
use App\Models\AboutMilestone;
use App\Models\AboutSection;
use App\Models\Banner;
use App\Models\BrandCase;
use App\Models\CalculatorOption;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use MoonShine\Laravel\Models\MoonshineUser;
use Tests\TestCase;

/**
 * Завантаження картинок через адмінку: PNG має доїжджати до диска
 * public_root і лягати в теку поля, а завеликий файл — давати зрозумілу
 * помилку валідації, а не 500 чи тишу.
 */
class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): MoonshineUser
    {
        return MoonshineUser::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => bcrypt('secret'),
            'moonshine_user_role_id' => 1,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function submit(string $resourceUri, int $id, array $payload): TestResponse
    {
        return $this->actingAs($this->admin(), 'moonshine')
            ->put("/admin/resource/$resourceUri/crud/$id", $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertUploads(string $resourceUri, int $id, string $column, array $payload, string $dir): void
    {
        $disk = Storage::fake('public_root');

        $this->submit($resourceUri, $id, [
            ...$payload,
            $column => UploadedFile::fake()->image('photo.png', 400, 300),
        ])->assertStatus(302)->assertSessionHasNoErrors();

        $files = $disk->allFiles($dir);

        $this->assertNotEmpty($files, "Файл не потрапив у $dir ($resourceUri)");
        $this->assertStringEndsWith('.png', $files[0]);
    }

    public function test_about_section_accepts_png(): void
    {
        $item = AboutSection::create(['title' => 'Виробництво', 'sort_order' => 4, 'is_active' => true]);

        $this->assertUploads('about-section-resource', $item->getKey(), 'image', ['title' => 'Виробництво'], 'images');

        $this->assertStringStartsWith('images/', $item->fresh()->image);
    }

    public function test_about_milestone_accepts_png(): void
    {
        $item = AboutMilestone::create(['year' => '2010', 'text' => 'Старт', 'placement' => 'top', 'sort_order' => 1, 'is_active' => true]);

        $this->assertUploads(
            'about-milestone-resource',
            $item->getKey(),
            'image',
            ['year' => '2010', 'text' => 'Старт', 'placement' => 'top'],
            'images',
        );
    }

    public function test_about_founder_accepts_png(): void
    {
        $item = AboutFounder::create(['name' => 'Засновник', 'is_active' => true]);

        $this->assertUploads('about-founder-resource', $item->getKey(), 'photo', ['name' => 'Засновник'], 'images');
    }

    public function test_banner_accepts_png(): void
    {
        $item = Banner::create(['title' => 'Банер', 'is_active' => true]);

        $this->assertUploads('banner-resource', $item->getKey(), 'image', ['title' => 'Банер'], 'banners');
    }

    public function test_team_member_accepts_png(): void
    {
        $item = TeamMember::create(['name' => 'Іван', 'position' => 'Технолог', 'is_active' => true]);

        $this->assertUploads('team-member-resource', $item->getKey(), 'image', ['name' => 'Іван', 'position' => 'Технолог'], 'team');
    }

    public function test_category_accepts_png(): void
    {
        $item = Category::create(['name' => 'Догляд', 'slug' => 'dogliad']);

        $this->assertUploads('category-resource', $item->getKey(), 'image', ['name' => 'Догляд', 'slug' => 'dogliad'], 'images');
    }

    public function test_post_accepts_png(): void
    {
        $item = Post::create(['title' => 'Стаття', 'slug' => 'stattya', 'body' => 'Текст']);

        $this->assertUploads(
            'post-resource',
            $item->getKey(),
            'image',
            ['title' => 'Стаття', 'slug' => 'stattya', 'body' => 'Текст'],
            'images/journal',
        );
    }

    public function test_product_accepts_png(): void
    {
        $category = Category::create(['name' => 'Догляд', 'slug' => 'dogliad']);
        $item = Product::create([
            'name' => 'Крем', 'slug' => 'krem', 'price' => 100, 'stock' => 1,
            'is_active' => true, 'category_id' => $category->getKey(),
        ]);

        $this->assertUploads(
            'product-resource',
            $item->getKey(),
            'image',
            ['name' => 'Крем', 'slug' => 'krem', 'price' => 100, 'stock' => 1, 'category_id' => $category->getKey()],
            'products',
        );
    }

    /**
     * Поля, які раніше були текстовим «Шлях до фото»: шлях вбивали руками,
     * і в базі лежало сміття на кшталт 'Test'.
     */
    public function test_brand_case_accepts_png(): void
    {
        $item = BrandCase::create(['brand_name' => 'Бренд', 'is_active' => true]);

        $this->assertUploads('brand-case-resource', $item->getKey(), 'image', ['brand_name' => 'Бренд'], 'images');
    }

    public function test_testimonial_accepts_png(): void
    {
        $item = Testimonial::create([
            'quote' => 'Коротко', 'text' => 'Текст відгуку', 'author_name' => 'Олена', 'is_active' => true,
        ]);

        $this->assertUploads(
            'testimonial-resource',
            $item->getKey(),
            'image',
            ['quote' => 'Коротко', 'text' => 'Текст відгуку', 'author_name' => 'Олена'],
            'images',
        );
    }

    public function test_calculator_option_accepts_png(): void
    {
        $item = CalculatorOption::create(['group' => 'tara', 'name' => 'Банка', 'value' => 10, 'is_active' => true]);

        $this->assertUploads(
            'calculator-option-resource',
            $item->getKey(),
            'image',
            ['group' => 'tara', 'name' => 'Банка', 'value' => 10],
            'images',
        );
    }

    /**
     * Понад 20 МБ — зрозуміла помилка валідації, а не 500 і не тиша.
     *
     * MoonShine складає помилки в іменований error bag (ім'я ресурсу),
     * тому третій аргумент assertSessionHasErrors обов'язковий.
     */
    public function test_oversized_file_is_rejected_with_validation_error(): void
    {
        $disk = Storage::fake('public_root');
        $item = AboutSection::create(['title' => 'Виробництво', 'sort_order' => 4, 'is_active' => true]);

        $this->submit('about-section-resource', $item->getKey(), [
            'title' => 'Виробництво',
            'image' => UploadedFile::fake()->create('huge.png', 25 * 1024, 'image/png'),
        ])->assertSessionHasErrors('image', null, 'about-section-resource');

        $this->assertEmpty($disk->allFiles('images'));
        $this->assertNull($item->fresh()->image);
    }

    /**
     * Велике фото після збереження стискається до 1920 px по довгій стороні:
     * ліміт завантаження 20 МБ, але класти такі файли на сайт не можна —
     * на проді 20 ГБ диска, і він уже одного разу забивався.
     */
    public function test_large_image_is_downscaled_after_save(): void
    {
        $disk = Storage::fake('public_root');
        $item = AboutSection::create(['title' => 'Виробництво', 'sort_order' => 4, 'is_active' => true]);

        $this->submit('about-section-resource', $item->getKey(), [
            'title' => 'Виробництво',
            'image' => UploadedFile::fake()->image('big.png', 3200, 2400),
        ])->assertStatus(302);

        $path = $item->fresh()->image;
        $this->assertNotNull($path);

        [$width, $height] = getimagesize($disk->path($path));

        $this->assertSame(1920, $width);
        $this->assertSame(1440, $height);
    }

    /**
     * Чужий формат теж не проходить мовчки.
     */
    public function test_non_image_is_rejected(): void
    {
        $item = AboutSection::create(['title' => 'Виробництво', 'sort_order' => 4, 'is_active' => true]);
        Storage::fake('public_root');

        $this->submit('about-section-resource', $item->getKey(), [
            'title' => 'Виробництво',
            'image' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('image', null, 'about-section-resource');
    }
}
