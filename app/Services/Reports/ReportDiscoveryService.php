<?php

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Reports\BaseReport;

class ReportDiscoveryService
{
    /**
     * Discover all reports in the application.
     *
     * @return Collection
     */
    public function discoverReports(): Collection
    {
        return Cache::remember('discovered_reports', 3600, function () {
            return $this->scanForReports();
        });
    }

    /**
     * Get reports grouped by module.
     *
     * @return Collection
     */
    public function getReportsByModule(): Collection
    {
        return $this->discoverReports()
            ->filter(fn($report) => $report->canAccess())
            ->groupBy(fn($report) => $report->getModule())
            ->map(fn($reports) => $reports->sortBy(fn($report) => $report->getName()));
    }

    /**
     * Get a specific report by its ID.
     *
     * @param string $reportId
     * @return BaseReport|null
     */
    public function getReportById(string $reportId): ?BaseReport
    {
        return $this->discoverReports()
            ->first(fn($report) => $report->getId() === $reportId);
    }

    /**
     * Scan the application for report classes.
     *
     * @return Collection
     */
    private function scanForReports(): Collection
    {
        $reports = collect();

        try {
            // Scan Cases directory for module-specific reports
            $casesPath = app_path('Cases');
            if (File::exists($casesPath)) {
                $reports = $reports->merge($this->scanDirectory($casesPath, true));
            }

            // Scan dedicated Reports directory
            $reportsPath = app_path('Reports');
            if (File::exists($reportsPath)) {
                $reports = $reports->merge($this->scanDirectory($reportsPath, false));
            }

        } catch (\Exception $e) {
            Log::error('Error discovering reports: ' . $e->getMessage());
        }

        return $reports;
    }

    /**
     * Scan a directory for report classes.
     *
     * @param string $directory
     * @param bool $isCasesDirectory
     * @return Collection
     */
    private function scanDirectory(string $directory, bool $isCasesDirectory = false): Collection
    {
        $reports = collect();
        
        $filesToScan = [];
        
        if ($isCasesDirectory) {
            $directories = File::directories($directory);
            foreach ($directories as $moduleDirectory) {
                $reportPath = $moduleDirectory . '/Reports';
                if (File::exists($reportPath)) {
                    $filesToScan = array_merge($filesToScan, File::allFiles($reportPath));
                }
            }
        } else {
            $filesToScan = File::allFiles($directory);
        }

        foreach ($filesToScan as $file) {
            $class = $this->getClassFromFile($file);

            if ($class && class_exists($class) && is_subclass_of($class, BaseReport::class)) {
                try {
                    $reflection = new \ReflectionClass($class);
                    if (!$reflection->isAbstract()) {
                        $reportInstance = new $class();
                        $key = $reportInstance->getId();
                        $reports->put($key, $reportInstance);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to instantiate report class {$class}: " . $e->getMessage());
                }
            }
        }

        return $reports;
    }

    /**
     * Get the class name from a file path.
     *
     * @param \SplFileInfo $file
     * @return string|null
     */
    private function getClassFromFile(\SplFileInfo $file): ?string
    {
        $namespace = 'App';
        
        // Normalize directory separators for Windows compatibility
        $normalizedAppPath = str_replace('\\', '/', app_path() . '/');
        $normalizedFilePath = str_replace('\\', '/', $file->getPathname());
        
        $relativePath = str_replace([$normalizedAppPath, '/', '.php'], ['', '\\', ''], $normalizedFilePath);
        return $namespace . '\\' . $relativePath;
    }

    /**
     * Clear the reports cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('discovered_reports');
    }
}
