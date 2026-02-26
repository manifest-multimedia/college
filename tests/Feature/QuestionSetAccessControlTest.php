<?php

namespace Tests\Feature;

use App\Models\QuestionSet;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuestionSetAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Administrator']);
        Role::create(['name' => 'Lecturer']);
    }

    public function test_lecturer_can_only_access_own_question_sets()
    {
        // Create users
        $lecturer1 = User::factory()->create(['name' => 'Lecturer One']);
        $lecturer2 = User::factory()->create(['name' => 'Lecturer Two']);

        $lecturer1->assignRole('Lecturer');
        $lecturer2->assignRole('Lecturer');

        // Create subject
        $subject = Subject::factory()->create();

        // Create question sets
        $questionSet1 = QuestionSet::factory()->create([
            'name' => 'Lecturer 1 Question Set',
            'created_by' => $lecturer1->id,
            'course_id' => $subject->id,
        ]);

        $questionSet2 = QuestionSet::factory()->create([
            'name' => 'Lecturer 2 Question Set',
            'created_by' => $lecturer2->id,
            'course_id' => $subject->id,
        ]);

        // Test lecturer 1 can access own question set
        $this->actingAs($lecturer1)
            ->get(route('question.sets.import', $questionSet1->id))
            ->assertStatus(200);

        // Test lecturer 1 cannot access lecturer 2's question set
        $this->actingAs($lecturer1)
            ->get(route('question.sets.import', $questionSet2->id))
            ->assertRedirect()
            ->assertSessionHas('error', 'You do not have permission to import questions to this question set.');
    }

    public function test_super_admin_can_access_all_question_sets()
    {
        // Create users
        $superAdmin = User::factory()->create(['name' => 'Super Admin']);
        $lecturer = User::factory()->create(['name' => 'Lecturer']);

        $superAdmin->assignRole('Super Admin');
        $lecturer->assignRole('Lecturer');

        // Create subject
        $subject = Subject::factory()->create();

        // Create question set by lecturer
        $questionSet = QuestionSet::factory()->create([
            'name' => 'Lecturer Question Set',
            'created_by' => $lecturer->id,
            'course_id' => $subject->id,
        ]);

        // Test super admin can access any question set
        $this->actingAs($superAdmin)
            ->get(route('question.sets.import', $questionSet->id))
            ->assertStatus(200);
    }

    public function test_administrator_can_access_all_question_sets()
    {
        // Create users
        $administrator = User::factory()->create(['name' => 'Administrator']);
        $lecturer = User::factory()->create(['name' => 'Lecturer']);

        $administrator->assignRole('Administrator');
        $lecturer->assignRole('Lecturer');

        // Create subject
        $subject = Subject::factory()->create();

        // Create question set by lecturer
        $questionSet = QuestionSet::factory()->create([
            'name' => 'Lecturer Question Set',
            'created_by' => $lecturer->id,
            'course_id' => $subject->id,
        ]);

        // Test administrator can access any question set
        $this->actingAs($administrator)
            ->get(route('question.sets.import', $questionSet->id))
            ->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_access_import()
    {
        // Create users
        $lecturer1 = User::factory()->create(['name' => 'Lecturer One']);
        $lecturer2 = User::factory()->create(['name' => 'Lecturer Two']);

        $lecturer1->assignRole('Lecturer');
        $lecturer2->assignRole('Lecturer');

        // Create subject
        $subject = Subject::factory()->create();

        // Create question set by lecturer 1
        $questionSet = QuestionSet::factory()->create([
            'name' => 'Lecturer 1 Question Set',
            'created_by' => $lecturer1->id,
            'course_id' => $subject->id,
        ]);

        // Test lecturer 2 cannot access import for lecturer 1's question set
        $this->actingAs($lecturer2)
            ->post(route('question.sets.import.preview', $questionSet->id), [
                'import_file' => \Illuminate\Http\UploadedFile::fake()->create('test.csv', 1, 'text/csv'),
                'format' => 'aiken',
            ])
            ->assertStatus(403)
            ->assertJson(['error' => 'You do not have permission to import to this question set.']);
    }
}
