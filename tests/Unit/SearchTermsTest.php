<?php

namespace Tests\Unit;

use App\Support\SearchTerms;
use PHPUnit\Framework\TestCase;

class SearchTermsTest extends TestCase
{
    public function test_splits_query_into_lowercase_words(): void
    {
        $this->assertSame(['крем', 'для', 'обличчя'], SearchTerms::tokens('  Крем  ДЛЯ обличчя '));
    }

    public function test_drops_too_short_words(): void
    {
        $this->assertSame(['крем'], SearchTerms::tokens('крем і з'));
    }

    /**
     * Якщо після відсіву не лишилось нічого, шукаємо рядок як є — інакше
     * запит «%» вимкнув би пошук і показав увесь каталог.
     */
    public function test_falls_back_to_whole_query_when_all_words_are_short(): void
    {
        $this->assertSame(['%'], SearchTerms::tokens('%'));
        $this->assertSame(['к'], SearchTerms::tokens('к'));
    }

    public function test_cuts_absurdly_long_token(): void
    {
        $this->assertSame(40, mb_strlen(SearchTerms::tokens(str_repeat('я', 100))[0]));
    }

    public function test_limits_number_of_words(): void
    {
        $this->assertCount(5, SearchTerms::tokens('одно двоє троє чотири п’ять шість сім'));
    }

    public function test_empty_query_gives_no_tokens(): void
    {
        $this->assertSame([], SearchTerms::tokens('   '));
        $this->assertSame([], SearchTerms::tokens(null));
    }

    public function test_escapes_like_wildcards(): void
    {
        $this->assertSame('100!% !_крем', SearchTerms::escapeLike('100% _крем'));
    }

    /** Сам символ екранування теж має екрануватися. */
    public function test_escapes_the_escape_character(): void
    {
        $this->assertSame('ого!!', SearchTerms::escapeLike('ого!'));
    }

    public function test_keyboard_layout_variant(): void
    {
        $this->assertContains('крем', SearchTerms::variants('rhtv'));
        $this->assertContains('rhtv', SearchTerms::variants('крем'));
    }

    public function test_transliteration_variant(): void
    {
        $this->assertContains('krem', SearchTerms::variants('крем'));
        $this->assertContains('крем', SearchTerms::variants('krem'));
    }

    /**
     * «shch» має стати «щ», а не розпастися на «sh» + «ch».
     */
    public function test_longest_transliteration_sequences_win(): void
    {
        $this->assertContains('щітка', SearchTerms::variants('shchitka'));
    }

    public function test_variants_have_no_duplicates(): void
    {
        $variants = SearchTerms::variants('spa');

        $this->assertSame($variants, array_values(array_unique($variants)));
    }
}
