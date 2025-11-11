<?php

namespace App\Services\Communication\Chat;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownRenderingService
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        // Configure the Environment with desired extensions
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 100,
            'table' => [
                'wrap' => [
                    'enabled' => true,
                    'tag' => 'div',
                    'attributes' => ['class' => 'table-responsive'],
                ],
            ],
        ]);

        // Add core extension
        $environment->addExtension(new CommonMarkCoreExtension());
        
        // Add GitHub Flavored Markdown for better formatting
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        
        // Add table extension
        $environment->addExtension(new TableExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Convert Markdown text to HTML
     */
    public function renderToHtml(string $markdown): string
    {
        return $this->converter->convert($markdown)->getContent();
    }

    /**
     * Check if text contains Markdown formatting
     */
    public function containsMarkdown(string $text): bool
    {
        // Check for common Markdown patterns
        $markdownPatterns = [
            '/^#{1,6}\s/m',                   // Headers
            '/\*\*.*?\*\*/',                  // Bold
            '/\*.*?\*/',                      // Italic  
            '/`.*?`/',                        // Inline code
            '/```[\s\S]*?```/',               // Code blocks
            '/^\s*[-*+]\s/m',                 // Unordered lists
            '/^\s*\d+\.\s/m',                 // Ordered lists
            '/\[.*?\]\(.*?\)/',               // Links
            '/!\[.*?\]\(.*?\)/',              // Images
            '/^\s*>\s/m',                     // Blockquotes
            '/^\s*\|.*\|/m',                  // Tables
            '/---+/',                         // Horizontal rules
        ];

        foreach ($markdownPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render markdown with safety checks and proper escaping
     */
    public function safeRender(string $text): string
    {
        // If no markdown detected, return escaped text with line breaks
        if (!$this->containsMarkdown($text)) {
            return nl2br(e($text));
        }

        // Render markdown to HTML
        $html = $this->renderToHtml($text);

        // Add Bootstrap classes to common elements for better styling
        $html = $this->addBootstrapClasses($html);

        return $html;
    }

    /**
     * Add Bootstrap classes to rendered HTML for better styling
     */
    private function addBootstrapClasses(string $html): string
    {
        // Add table classes
        $html = preg_replace('/<table>/', '<table class="table table-sm table-bordered">', $html);
        
        // Add code block classes
        $html = preg_replace('/<pre><code(?:\s+class="language-([^"]*)")?[^>]*>/', '<pre class="bg-light p-3 rounded"><code class="language-$1">', $html);
        
        // Add inline code classes
        $html = preg_replace('/<code>/', '<code class="bg-light px-1 rounded">', $html);
        
        // Add blockquote classes
        $html = preg_replace('/<blockquote>/', '<blockquote class="blockquote border-start border-4 border-primary ps-3 my-3">', $html);
        
        // Add list classes
        $html = preg_replace('/<ul>/', '<ul class="list-unstyled">', $html);
        $html = preg_replace('/<ol>/', '<ol class="list-group list-group-numbered">', $html);
        
        // Add heading classes
        $html = preg_replace('/<h([1-6])>/', '<h$1 class="mt-3 mb-2">', $html);
        
        // Add paragraph spacing
        $html = preg_replace('/<p>/', '<p class="mb-2">', $html);

        return $html;
    }

    /**
     * Strip markdown formatting for plain text display
     */
    public function stripMarkdown(string $markdown): string
    {
        // Convert to HTML first then strip tags
        $html = $this->renderToHtml($markdown);
        return strip_tags($html);
    }
}