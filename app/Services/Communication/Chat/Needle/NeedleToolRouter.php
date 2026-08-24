<?php

namespace App\Services\Communication\Chat\Needle;

use App\Services\Communication\Chat\MCPIntegrationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Routes straightforward, read-only AI Sensei requests to a locally hosted
 * Needle instance. All resulting calls still pass through MCPIntegrationService
 * so that permissions and business rules remain the source of truth.
 */
class NeedleToolRouter
{
    public function __construct(private readonly MCPIntegrationService $mcpIntegrationService) {}

    /**
     * @return array{handled: bool, response?: string, reason?: string, confidence?: float|null}
     */
    public function handle(string $message): array
    {
        if (! config('services.needle.enabled')) {
            return ['handled' => false, 'reason' => 'disabled'];
        }

        $tools = $this->mcpIntegrationService->getNeedleToolsConfig();

        if ($tools === []) {
            return ['handled' => false, 'reason' => 'no_tools'];
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.needle.timeout', 5))
                ->post(rtrim((string) config('services.needle.url'), '/').'/complete', [
                    'query' => $message,
                    'tools' => $tools,
                    'system' => 'assistant: College360 AI Sensei; locale: en-GH',
                ]);
        } catch (ConnectionException $exception) {
            Log::notice('Needle is unavailable; escalating AI Sensei request.', [
                'error' => $exception->getMessage(),
            ]);

            return ['handled' => false, 'reason' => 'unavailable'];
        }

        if (! $response->successful()) {
            Log::warning('Needle returned an unsuccessful response; escalating AI Sensei request.', [
                'status' => $response->status(),
            ]);

            return ['handled' => false, 'reason' => 'unavailable'];
        }

        $payload = $response->json();
        $confidence = isset($payload['confidence']) ? (float) $payload['confidence'] : null;
        $minimumConfidence = (float) config('services.needle.minimum_confidence', 0.90);
        $calls = $payload['function_calls'] ?? [];

        if (($payload['type'] ?? null) !== 'call' || $calls === [] || $confidence === null || $confidence < $minimumConfidence) {
            return [
                'handled' => false,
                'reason' => $calls === [] ? 'no_match' : 'low_confidence',
                'confidence' => $confidence,
            ];
        }

        $results = [];
        foreach ($calls as $call) {
            $name = $call['name'] ?? null;
            $arguments = $call['arguments'] ?? null;

            if (! is_string($name) || ! is_array($arguments)) {
                return ['handled' => false, 'reason' => 'invalid_call', 'confidence' => $confidence];
            }

            // This is intentionally redundant with the sidecar schema. The PHP
            // allow-list protects the application if the sidecar is misconfigured.
            if (! in_array($name, MCPIntegrationService::needleReadOnlyToolNames(), true)) {
                return ['handled' => false, 'reason' => 'write_tool', 'confidence' => $confidence];
            }

            $results[] = [
                'tool' => $name,
                'result' => $this->mcpIntegrationService->processFunctionCall($name, $arguments),
            ];
        }

        Log::info('AI Sensei request completed locally by Needle.', [
            'tools' => array_column($results, 'tool'),
            'confidence' => $confidence,
        ]);

        return [
            'handled' => true,
            'confidence' => $confidence,
            'response' => $this->formatResults($results),
        ];
    }

    private function formatResults(array $results): string
    {
        $successful = collect($results)->every(fn (array $item) => ($item['result']['success'] ?? false) === true);
        $heading = $successful
            ? 'AI Sensei completed this request using secure local institutional tools:'
            : 'AI Sensei could not complete every local lookup:';

        return $heading."\n\n```json\n".json_encode(
            $results,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        )."\n```";
    }
}
