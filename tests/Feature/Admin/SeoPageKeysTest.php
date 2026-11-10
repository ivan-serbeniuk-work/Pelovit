<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Setting;
use Tests\TestCase;

/**
 * SEO-ключ сторінки живе у двох місцях: pageKey у composable useSeoPage на
 * вітрині й Setting::SEO_PAGES в адмінці. Якщо вони розійдуться, поле в
 * адмінці нікуди не підключене (або сторінку взагалі не можна відредагувати
 * без деплою — саме так сталося з /promotions і /obmin-ta-povernennya).
 */
class SeoPageKeysTest extends TestCase
{
    public function test_every_storefront_page_key_is_editable_in_admin(): void
    {
        $missing = array_diff($this->storefrontPageKeys(), array_keys(Setting::SEO_PAGES));

        $this->assertSame([], array_values($missing), 'У вітрині є pageKey без полів в адмінці: '
            .implode(', ', $missing).'. Додайте їх у Setting::SEO_PAGES.');
    }

    public function test_admin_has_no_page_keys_the_storefront_never_asks_for(): void
    {
        $extra = array_diff(array_keys(Setting::SEO_PAGES), $this->storefrontPageKeys());

        $this->assertSame([], array_values($extra), 'В адмінці є сторінки, яких немає у вітрині: '
            .implode(', ', $extra).'. Приберіть їх із Setting::SEO_PAGES.');
    }

    public function test_save_route_accepts_keys_of_every_page(): void
    {
        foreach (array_keys(Setting::SEO_PAGES) as $page) {
            foreach (['title', 'description', 'og_image'] as $field) {
                $this->assertContains("seo_{$page}_{$field}", Setting::seoPageKeys());
            }
        }
    }

    /**
     * @return list<string>
     */
    private function storefrontPageKeys(): array
    {
        $dir = base_path('frontend/app/pages');

        if (! is_dir($dir)) {
            $this->markTestSkipped('Вітрина не встановлена — немає чого перевіряти.');
        }

        $keys = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'vue') {
                continue;
            }

            if (preg_match_all('/pageKey:\s*[\'"]([\w-]+)[\'"]/', (string) file_get_contents($file->getPathname()), $m)) {
                $keys = [...$keys, ...$m[1]];
            }
        }

        sort($keys);

        return array_values(array_unique($keys));
    }
}
