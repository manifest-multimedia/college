<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PaginationThemeTest extends TestCase
{
    public function test_livewire_uses_bootstrap_pagination_theme_globally(): void
    {
        $this->assertSame('bootstrap', config('livewire.pagination_theme'));
    }

    public function test_laravel_paginator_renders_bootstrap_markup_globally(): void
    {
        $paginator = new LengthAwarePaginator(
            collect(range(1, 30))->forPage(1, 15),
            30,
            15,
            1,
            ['path' => '/pagination-test']
        );

        $html = $paginator->links()->toHtml();

        $this->assertStringContainsString('page-item', $html);
        $this->assertStringContainsString('page-link', $html);
    }
}
