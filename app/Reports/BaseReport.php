<?php

namespace App\Reports;

use Illuminate\Support\Facades\Auth;

abstract class BaseReport
{
    /**
     * The report's display name (e.g., "Student Performance Summary").
     */
    abstract public function getName(): string;

    /**
     * A brief user-friendly description of what the report does.
     */
    abstract public function getDescription(): string;

    /**
     * The module this report belongs to (e.g., "Academics", "Finance").
     */
    abstract public function getModule(): string;

    /**
     * The icon class for the report.
     */
    public function getIcon(): string
    {
        return 'fas fa-chart-bar';
    }

    /**
     * Defines the filters used to generate the report UI dynamically.
     *
     * @return array
     */
    abstract public function getFilters(): array;

    /**
     * Defines the column headers for the report output.
     *
     * @return array
     */
    abstract public function getColumns(): array;

    /**
     * The core logic to fetch and process the report data.
     *
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    abstract public function generateData(array $filters = []): \Illuminate\Support\Collection;

    /**
     * Check if the current user can access this report.
     * By default, any authenticated user can access. You can override this in specific reports.
     *
     * @return bool
     */
    public function canAccess(): bool
    {
        return Auth::check();
    }

    /**
     * Get export formats supported by this report.
     *
     * @return array
     */
    public function exportFormats(): array
    {
        return ['pdf', 'excel'];
    }

    /**
     * Get the report's unique identifier.
     *
     * @return string
     */
    public function getId(): string
    {
        return str_replace('\\', '_', static::class);
    }

    /**
     * Get validation rules for filters.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        $rules = [];
        foreach ($this->getFilters() as $filter) {
            $rule = [];
            $isRequired = $filter['required'] ?? false;
            
            if ($isRequired) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }
            
            if (($filter['type'] ?? '') === 'date') {
                $rule[] = 'date';
            }
            
            if (($filter['type'] ?? '') === 'select' && isset($filter['options']) && !empty($filter['options'])) {
                $rule[] = 'in:' . implode(',', array_keys($filter['options']));
            }
            
            if (!empty($rule)) {
                $rules[$filter['key']] = implode('|', $rule);
            }
        }
        return $rules;
    }

    /**
     * Get default filter values.
     *
     * @return array
     */
    public function getDefaultFilters(): array
    {
        $defaults = [];
        foreach ($this->getFilters() as $filter) {
            if (isset($filter['default'])) {
                $defaults[$filter['key']] = $filter['default'];
            }
        }
        return $defaults;
    }

    /**
     * Get required filter labels for error messages.
     *
     * @return array
     */
    public function getRequiredFilterLabels(): array
    {
        $required = [];
        foreach ($this->getFilters() as $filter) {
            if ($filter['required'] ?? false) {
                $required[] = $filter['label'];
            }
        }
        return $required;
    }
}
