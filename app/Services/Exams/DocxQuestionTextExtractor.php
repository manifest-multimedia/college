<?php

namespace App\Services\Exams;

use RuntimeException;
use ZipArchive;

class DocxQuestionTextExtractor
{
    /**
     * Extract paragraph text from a DOCX document without rendering or executing
     * any embedded document content. The import parser intentionally works only
     * with the document's text layer.
     */
    public function extract(string $path): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Word imports require the PHP ZIP extension to be enabled.');
        }

        if (! class_exists(\DOMDocument::class)) {
            throw new RuntimeException('Word imports require the PHP DOM extension to be enabled.');
        }

        $archive = new ZipArchive;
        if ($archive->open($path) !== true) {
            throw new RuntimeException('The uploaded file is not a readable DOCX document.');
        }

        try {
            $documentStats = $archive->statName('word/document.xml');
            if ($documentStats === false || ($documentStats['size'] ?? 0) > 15 * 1024 * 1024) {
                throw new RuntimeException('The Word document text is missing or too large to import safely.');
            }

            $documentXml = $archive->getFromName('word/document.xml');
        } finally {
            $archive->close();
        }

        if ($documentXml === false) {
            throw new RuntimeException('The uploaded file does not contain Word document text.');
        }

        $previousUseInternalErrors = libxml_use_internal_errors(true);
        try {
            $document = new \DOMDocument;
            if (! $document->loadXML($documentXml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
                throw new RuntimeException('The Word document text could not be read.');
            }

            $xpath = new \DOMXPath($document);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            $paragraphs = $xpath->query('//w:body//w:p');
            $lines = [];

            foreach ($paragraphs as $paragraph) {
                $line = '';
                foreach ($xpath->query('.//w:t | .//w:tab | .//w:br | .//w:cr', $paragraph) as $node) {
                    $line .= match ($node->localName) {
                        'tab' => "\t",
                        'br', 'cr' => "\n",
                        default => $node->textContent,
                    };
                }

                $lines[] = trim(str_replace("\u{00A0}", ' ', $line));
            }

            return implode("\n", $lines);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }
    }
}
