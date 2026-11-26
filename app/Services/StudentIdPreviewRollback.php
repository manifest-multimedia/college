<?php

namespace App\Services;

final class StudentIdPreviewRollback extends \RuntimeException
{
    public function __construct(public readonly array $results)
    {
        parent::__construct('Rollback student ID preview');
    }
}
