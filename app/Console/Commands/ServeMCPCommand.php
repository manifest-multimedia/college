<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Communication\Chat\MCP\ExamManagementMCPService;
use Illuminate\Support\Facades\Log;

class ServeMCPCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:serve {--host=localhost} {--port=3000}';

    /**
     * The console command description.
     */
    protected $description = 'Start the MCP (Model Context Protocol) server for exam management';

    protected ExamManagementMCPService $mcpService;

    public function __construct(ExamManagementMCPService $mcpService)
    {
        parent::__construct();
        $this->mcpService = $mcpService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = $this->option('host');
        $port = $this->option('port');

        $this->info("Starting MCP Server on {$host}:{$port}");
        $this->info("Available tools:");
        
        foreach ($this->mcpService->getTools() as $tool) {
            $this->line("- {$tool['name']}: {$tool['description']}");
        }

        $this->info("\nListening for MCP requests...");

        // Simple HTTP server to handle MCP requests
        $this->startServer($host, $port);
    }

    /**
     * Start the MCP server
     */
    private function startServer(string $host, int $port): void
    {
        // Create socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, $host, $port);
        socket_listen($socket, 5);

        $this->info("MCP Server running at http://{$host}:{$port}");
        $this->info("Press Ctrl+C to stop");

        while (true) {
            $client = socket_accept($socket);
            
            if ($client) {
                $this->handleRequest($client);
                socket_close($client);
            }
        }

        socket_close($socket);
    }

    /**
     * Handle incoming MCP request
     */
    private function handleRequest($client): void
    {
        try {
            // Read HTTP request
            $request = '';
            while ($line = socket_read($client, 1024)) {
                $request .= $line;
                if (strpos($request, "\r\n\r\n") !== false) {
                    break;
                }
            }

            // Parse request
            $lines = explode("\r\n", $request);
            $firstLine = $lines[0] ?? '';
            
            if (strpos($firstLine, 'POST') === 0) {
                // Extract JSON body
                $bodyStart = strpos($request, "\r\n\r\n") + 4;
                $body = substr($request, $bodyStart);
                
                $this->handleMCPRequest($client, $body);
            } elseif (strpos($firstLine, 'GET') === 0) {
                // Handle health check or capabilities request
                $this->handleGetRequest($client, $firstLine);
            } else {
                $this->sendResponse($client, 405, 'Method Not Allowed');
            }
        } catch (\Exception $e) {
            Log::error('MCP Server Error', ['error' => $e->getMessage()]);
            $this->sendResponse($client, 500, 'Internal Server Error');
        }
    }

    /**
     * Handle MCP POST requests
     */
    private function handleMCPRequest($client, string $body): void
    {
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse($client, 400, 'Invalid JSON');
            return;
        }

        // Handle different MCP message types
        switch ($data['method'] ?? '') {
            case 'initialize':
                $response = $this->handleInitialize($data);
                break;
            case 'tools/list':
                $response = $this->handleToolsList($data);
                break;
            case 'tools/call':
                $response = $this->handleToolCall($data);
                break;
            default:
                $response = [
                    'jsonrpc' => '2.0',
                    'id' => $data['id'] ?? null,
                    'error' => [
                        'code' => -32601,
                        'message' => 'Method not found'
                    ]
                ];
        }

        $this->sendJSONResponse($client, $response);
    }

    /**
     * Handle GET requests (capabilities, health check)
     */
    private function handleGetRequest($client, string $requestLine): void
    {
        if (strpos($requestLine, '/capabilities') !== false) {
            $capabilities = [
                'server' => [
                    'name' => 'Exam Management MCP Server',
                    'version' => '1.0.0'
                ],
                'capabilities' => [
                    'tools' => true
                ],
                'tools' => $this->mcpService->getTools()
            ];
            
            $this->sendJSONResponse($client, $capabilities);
        } elseif (strpos($requestLine, '/health') !== false) {
            $this->sendJSONResponse($client, ['status' => 'healthy', 'timestamp' => now()->toISOString()]);
        } else {
            $this->sendResponse($client, 404, 'Not Found');
        }
    }

    /**
     * Handle MCP initialize request
     */
    private function handleInitialize(array $data): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $data['id'] ?? null,
            'result' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [
                    'tools' => [
                        'listChanged' => false
                    ]
                ],
                'serverInfo' => [
                    'name' => 'Exam Management MCP Server',
                    'version' => '1.0.0'
                ]
            ]
        ];
    }

    /**
     * Handle tools list request
     */
    private function handleToolsList(array $data): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $data['id'] ?? null,
            'result' => [
                'tools' => $this->mcpService->getTools()
            ]
        ];
    }

    /**
     * Handle tool call request
     */
    private function handleToolCall(array $data): array
    {
        $params = $data['params'] ?? [];
        $toolName = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];

        $this->line("Tool call: {$toolName}");
        
        $result = $this->mcpService->handleToolCall($toolName, $arguments);

        return [
            'jsonrpc' => '2.0',
            'id' => $data['id'] ?? null,
            'result' => [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode($result, JSON_PRETTY_PRINT)
                    ]
                ]
            ]
        ];
    }

    /**
     * Send HTTP response
     */
    private function sendResponse($client, int $code, string $message, string $contentType = 'text/plain'): void
    {
        $response = "HTTP/1.1 {$code} {$message}\r\n";
        $response .= "Content-Type: {$contentType}\r\n";
        $response .= "Content-Length: " . strlen($message) . "\r\n";
        $response .= "Access-Control-Allow-Origin: *\r\n";
        $response .= "Access-Control-Allow-Methods: GET, POST, OPTIONS\r\n";
        $response .= "Access-Control-Allow-Headers: Content-Type\r\n";
        $response .= "\r\n";
        $response .= $message;

        socket_write($client, $response);
    }

    /**
     * Send JSON response
     */
    private function sendJSONResponse($client, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $this->sendResponse($client, 200, $json, 'application/json');
    }
}