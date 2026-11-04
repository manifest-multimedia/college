<?php

namespace Tests\Feature;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CanteenMenuTest extends TestCase
{
    private function menu(bool $meals, bool $lists, string $routeName): string
    {
        Gate::swap(\Mockery::mock(\Illuminate\Contracts\Auth\Access\Gate::class));
        Gate::shouldReceive('any')->with(['manage-canteen-meals', 'manage-canteen-qualifications'])->andReturn($meals || $lists);
        Gate::shouldReceive('check')->with('manage-canteen-meals')->andReturn($meals);
        Gate::shouldReceive('check')->with('manage-canteen-qualifications')->andReturn($lists);
        $route = (new Route('GET', '/menu-preview', fn () => null))->name($routeName);
        request()->setRouteResolver(fn () => $route);

        return view('components.app.canteen-menu')->render();
    }

    public function test_canteen_links_share_one_expanded_parent_with_the_current_page_active(): void
    {
        $html = $this->menu(true, true, 'canteen.meals.activity');
        $this->assertSame(1, substr_count($html, '>Canteen Module<'));
        $this->assertStringContainsString('menu-item menu-accordion show', $html);
        $this->assertStringContainsString('class="menu-link active" href="'.route('canteen.meals.activity').'"', $html);
        $this->assertStringContainsString('Meal Windows', $html);
        $this->assertStringContainsString('Meal Activity', $html);
        $this->assertStringContainsString('Qualification Lists', $html);
    }

    public function test_individual_permissions_control_the_child_links(): void
    {
        $html = $this->menu(false, true, 'canteen.lists.index');
        $this->assertStringContainsString('Canteen Module', $html);
        $this->assertStringContainsString('Qualification Lists', $html);
        $this->assertStringNotContainsString('Meal Windows', $html);
        $html = $this->menu(true, false, 'students');
        $this->assertStringContainsString('Meal Windows', $html);
        $this->assertStringNotContainsString('Qualification Lists', $html);
        $this->assertStringNotContainsString('menu-accordion show', $html);
    }

    public function test_empty_canteen_menu_is_hidden(): void
    {
        $this->assertSame('', trim($this->menu(false, false, 'students')));
    }
}
