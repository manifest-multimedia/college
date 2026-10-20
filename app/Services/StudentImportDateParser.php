<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentImportDateParser
{
    public function normalize(mixed $value, string $format = 'auto'): ?string
    {
        $candidates = $this->candidates($value);
        if ($format === 'dmy' || $format === 'mdy') {
            return $candidates[$format];
        }
        $possible = array_values(array_unique(array_filter($candidates)));

        return count($possible) === 1 ? $possible[0] : null;
    }

    public function inspect(iterable $values): array
    {
        $analysis = ['total' => 0, 'dmy' => 0, 'mdy' => 0, 'ambiguous' => 0, 'independent' => 0, 'invalid' => 0, 'examples' => []];
        foreach ($values as $value) {
            if ($value === null || (is_string($value) && trim($value) === '')) {
                continue;
            }
            $analysis['total']++;
            $candidates = $this->candidates($value);
            $kind = match (true) {
                $candidates['dmy'] === null && $candidates['mdy'] === null => 'invalid',
                $candidates['mdy'] === null => 'dmy',
                $candidates['dmy'] === null => 'mdy',
                $candidates['dmy'] !== $candidates['mdy'] => 'ambiguous',
                default => 'independent',
            };
            $analysis[$kind]++;
            if (count($analysis['examples']) < 3) {
                $analysis['examples'][] = [
                    'value' => $value instanceof DateTimeInterface ? $value->format('Y-m-d') : (string) $value,
                    ...$candidates,
                ];
            }
        }

        return $analysis;
    }

    public function combine(array $left, array $right): array
    {
        foreach (['total', 'dmy', 'mdy', 'ambiguous', 'independent', 'invalid'] as $key) {
            $left[$key] += $right[$key];
        }
        $left['examples'] = array_slice([...$left['examples'], ...$right['examples']], 0, 3);

        return $left;
    }

    public function status(array $analysis): string
    {
        return match (true) {
            $analysis['dmy'] > 0 && $analysis['mdy'] > 0 => 'mixed',
            $analysis['dmy'] > 0 || $analysis['mdy'] > 0 => 'detected',
            $analysis['ambiguous'] > 0 => 'ambiguous',
            $analysis['total'] > 0 && $analysis['invalid'] === $analysis['total'] => 'invalid',
            $analysis['total'] > 0 => 'independent',
            default => 'empty',
        };
    }

    public function resolve(array $analysis, string $selection = 'auto'): string
    {
        if (! in_array($selection, ['auto', 'dmy', 'mdy'], true)) {
            throw new InvalidArgumentException('Choose automatic detection, day/month/year or month/day/year.');
        }
        if ($selection !== 'auto') {
            return $selection;
        }
        if ($this->status($analysis) === 'mixed') {
            throw new InvalidArgumentException('Conflicting day-first and month-first dates were found. Correct the column or select one format; incompatible rows will be reported as invalid.');
        }
        if ($this->status($analysis) === 'ambiguous') {
            throw new InvalidArgumentException('The date format is ambiguous. Select day/month/year or month/day/year after reviewing the examples.');
        }

        return $analysis['dmy'] > 0 ? 'dmy' : ($analysis['mdy'] > 0 ? 'mdy' : 'auto');
    }

    private function candidates(mixed $value): array
    {
        $invalid = ['dmy' => null, 'mdy' => null];
        if ($value instanceof DateTimeInterface) {
            return array_fill_keys(['dmy', 'mdy'], $value->format('Y-m-d'));
        }
        if (is_numeric($value)) {
            if (! is_finite((float) $value) || (float) $value <= 0) {
                return $invalid;
            }
            try {
                $value = Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable) {
                return $invalid;
            }
        }
        if (! is_string($value)) {
            return $invalid;
        }
        $value = trim($value);
        if (preg_match('/^(\d{4})([-\/])(\d{1,2})\2(\d{1,2})$/D', $value, $parts)) {
            $date = $this->calendarDate((int) $parts[1], (int) $parts[3], (int) $parts[4]);

            return array_fill_keys(['dmy', 'mdy'], $date);
        }
        if (preg_match('/^(\d{1,2})([-\/.])(\d{1,2})\2(\d{4})$/D', $value, $parts)) {
            return [
                'dmy' => $this->calendarDate((int) $parts[4], (int) $parts[3], (int) $parts[1]),
                'mdy' => $this->calendarDate((int) $parts[4], (int) $parts[1], (int) $parts[3]),
            ];
        }
        if (preg_match('/^(\d{1,2} [A-Za-z]+ \d{4}|[A-Za-z]+ \d{1,2},? \d{4})$/D', $value)) {
            foreach (['j F Y', 'j M Y', 'F j, Y', 'M j, Y', 'F j Y', 'M j Y'] as $format) {
                $date = DateTimeImmutable::createFromFormat('!'.$format, $value);
                $errors = DateTimeImmutable::getLastErrors();
                if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                    return array_fill_keys(['dmy', 'mdy'], $date->format('Y-m-d'));
                }
            }
        }

        return $invalid;
    }

    private function calendarDate(int $year, int $month, int $day): ?string
    {
        return checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : null;
    }
}
