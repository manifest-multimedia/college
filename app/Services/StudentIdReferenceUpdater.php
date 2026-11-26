<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

final class StudentIdReferenceUpdater
{
    /** Update live string references without confusing IDs exchanged by two students. */
    public function apply(array $mapping): void
    {
        $changed = array_values(array_filter($mapping, fn ($row) => $row['old_id'] !== $row['new_id']));
        if (! $changed) {
            return;
        }
        // Offline exam/payment clients may still submit old identifiers. Never
        // transfer an identifier to another student, even within the same batch.
        $owners = [];
        foreach ($mapping as $row) {
            if (filled($row['old_id'])) {
                $owners[strtolower($row['old_id'])] = $row['id'];
            }
        }
        foreach ($changed as $row) {
            $owner = $owners[strtolower($row['new_id'])] ?? $row['id'];
            if ($owner !== $row['id'] || DB::table('student_id_changes')->where('student_id', '!=', $row['id'])->where(function ($query) use ($row) {
                $query->where('old_student_id', $row['new_id'])->orWhere('new_student_id', $row['new_id']);
            })->exists()) {
                throw new RuntimeException('A proposed ID belongs or previously belonged to another student. Choose a different pattern or sequence range.');
            }
        }
        // These tables use the public identifier as a live lookup or uniqueness key.
        foreach (['fee_collections' => 'student_id', 'election_votes' => 'student_id', 'election_voting_sessions' => 'student_id', 'canteen_meal_redemptions' => 'index_number'] as $table => $column) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $oldIds = array_values(array_filter(array_column($changed, 'old_id'), fn ($id) => $id !== null && $id !== ''));
            $newIds = array_column($changed, 'new_id');
            // An unowned historical reference at a target ID must not be attached to a different student.
            if (DB::table($table)->whereIn($column, $newIds)->whereNotIn($column, $oldIds)->lockForUpdate()->first()) {
                throw new RuntimeException("Existing reference conflicts with a proposed ID in {$table}.");
            }
            $temporary = [];
            foreach ($changed as $row) {
                if ($row['old_id'] === null || $row['old_id'] === '') {
                    continue;
                }
                $query = DB::table($table)->where($column, $row['old_id']);
                if ($table === 'canteen_meal_redemptions' && $query->clone()->whereNotNull('student_id')->where('student_id', '!=', $row['id'])->exists()) {
                    throw new RuntimeException('Canteen redemption identity conflict.');
                }
                $temp = 'REGEN-'.Str::uuid();
                $query->update([$column => $temp]);
                $temporary[] = [$temp, $row['new_id']];
            }
            foreach ($temporary as [$temp, $newId]) {
                DB::table($table)->where($column, $temp)->update([$column => $newId]);
            }
        }
    }
}
