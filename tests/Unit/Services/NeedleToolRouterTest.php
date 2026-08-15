<?php

namespace Tests\Unit\Services;

use App\Services\Communication\Chat\MCPIntegrationService;
use App\Services\Communication\Chat\Needle\NeedleToolRouter;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class NeedleToolRouterTest extends TestCase
{
    public function test_it_executes_a_high_confidence_read_only_tool_locally(): void
    {
        config()->set('services.needle.enabled', true);
        config()->set('services.needle.url', 'http://needle.test');
        config()->set('services.needle.minimum_confidence', 0.90);

        Http::fake([
            'http://needle.test/complete' => Http::response([
                'type' => 'call',
                'confidence' => 0.96,
                'function_calls' => [[
                    'name' => 'list_courses',
                    'arguments' => [],
                ]],
            ]),
        ]);

        $mcp = Mockery::mock(MCPIntegrationService::class);
        $mcp->shouldReceive('getNeedleToolsConfig')->once()->andReturn([[
            'name' => 'list_courses',
            'description' => 'Get courses.',
            'parameters' => ['type' => 'object', 'properties' => []],
        ]]);
        $mcp->shouldReceive('processFunctionCall')
            ->once()
            ->with('list_courses', [])
            ->andReturn(['success' => true, 'courses' => []]);

        $result = (new NeedleToolRouter($mcp))->handle('Show me all courses');

        $this->assertTrue($result['handled']);
        $this->assertSame(0.96, $result['confidence']);
        $this->assertStringContainsString('list_courses', $result['response']);
        Http::assertSent(fn ($request) => $request->url() === 'http://needle.test/complete'
            && $request['query'] === 'Show me all courses');
    }

    public function test_it_escalates_low_confidence_calls_without_executing_a_tool(): void
    {
        config()->set('services.needle.enabled', true);
        config()->set('services.needle.url', 'http://needle.test');
        config()->set('services.needle.minimum_confidence', 0.90);

        Http::fake([
            'http://needle.test/complete' => Http::response([
                'type' => 'call',
                'confidence' => 0.50,
                'function_calls' => [[
                    'name' => 'list_courses',
                    'arguments' => [],
                ]],
            ]),
        ]);

        $mcp = Mockery::mock(MCPIntegrationService::class);
        $mcp->shouldReceive('getNeedleToolsConfig')->once()->andReturn([['name' => 'list_courses']]);
        $mcp->shouldNotReceive('processFunctionCall');

        $result = (new NeedleToolRouter($mcp))->handle('Show me all courses');

        $this->assertFalse($result['handled']);
        $this->assertSame('low_confidence', $result['reason']);
    }
}
