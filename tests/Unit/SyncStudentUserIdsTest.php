<?php

namespace Tests\Unit;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncStudentUserIdsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test syncing students with existing user accounts.
     */
    public function test_sync_students_with_existing_users()
    {
        // Create a user account
        $user = User::factory()->create([
            'email' => 'existing-student@example.com',
        ]);

        // Create a student with matching email but no user_id
        $student = Student::create([
            'first_name' => 'Existing',
            'last_name' => 'Student',
            'email' => 'existing-student@example.com',
            // Other required fields...
        ]);

        // Run the command
        $this->artisan('students:sync-user-ids')
            ->expectsOutput('Sync completed!')
            ->assertExitCode(0);

        // Reload the student from the database
        $student->refresh();

        // Student should now have the correct user_id
        $this->assertEquals($user->id, $student->user_id);
    }

    /**
     * Test creating new user accounts for students.
     */
    public function test_create_new_users_for_students()
    {
        // Create a student with no matching user
        $student = Student::create([
            'first_name' => 'New',
            'last_name' => 'Student',
            'email' => 'new-student@example.com',
            // Other required fields...
        ]);

        // Make sure there's no user with this email yet
        $this->assertNull(User::where('email', 'new-student@example.com')->first());

        // Run the command
        $this->artisan('students:sync-user-ids')
            ->expectsOutput('Sync completed!')
            ->assertExitCode(0);

        // Reload the student from the database
        $student->refresh();

        // There should be a new user with the student's email
        $user = User::where('email', 'new-student@example.com')->first();
        $this->assertNotNull($user);

        // Student should now have the correct user_id
        $this->assertEquals($user->id, $student->user_id);
    }

    /**
     * Test dry run mode.
     */
    public function test_dry_run_mode()
    {
        // Create a student with no matching user
        $student = Student::create([
            'first_name' => 'Dry',
            'last_name' => 'Run',
            'email' => 'dry-run@example.com',
            // Other required fields...
        ]);

        // Run the command in dry-run mode
        $this->artisan('students:sync-user-ids --dry-run')
            ->expectsOutput('DRY RUN MODE: No database changes will be made')
            ->expectsOutput('This was a dry run. No actual changes were made to the database.')
            ->assertExitCode(0);

        // Reload the student from the database
        $student->refresh();

        // Student should still have null user_id
        $this->assertNull($student->user_id);

        // No user should have been created
        $this->assertNull(User::where('email', 'dry-run@example.com')->first());
    }

    /**
     * Test handling of invalid emails.
     */
    public function test_invalid_emails()
    {
        // Create a student with an invalid email
        $student = Student::create([
            'first_name' => 'Invalid',
            'last_name' => 'Email',
            'email' => 'not-a-valid-email',
            // Other required fields...
        ]);

        // Run the command
        $this->artisan('students:sync-user-ids')
            ->expectsOutput('Sync completed!')
            ->assertExitCode(0);

        // Reload the student from the database
        $student->refresh();

        // Student should still have null user_id as the email is invalid
        $this->assertNull($student->user_id);

        // No user should have been created
        $this->assertNull(User::where('email', 'not-a-valid-email')->first());
    }

    /**
     * Test batch processing.
     */
    public function test_batch_processing()
    {
        // Create 25 students
        for ($i = 1; $i <= 25; $i++) {
            Student::create([
                'first_name' => "Student{$i}",
                'last_name' => 'Batch',
                'email' => "batch-student{$i}@example.com",
                // Other required fields...
            ]);
        }

        // Run the command with batch size of 10
        $this->artisan('students:sync-user-ids --batch=10')
            ->expectsOutput('Sync completed!')
            ->assertExitCode(0);

        // All 25 students should have user accounts
        $this->assertEquals(25, User::where('email', 'like', 'batch-student%@example.com')->count());
    }
}
