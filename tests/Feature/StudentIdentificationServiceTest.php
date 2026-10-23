<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentIdentification;
use App\Services\StudentIdentificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentIdentificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_encrypts_and_masks_a_ghana_card_number(): void
    {
        $student = Student::create([
            'first_name' => 'Ama',
            'last_name' => 'Mensah',
            'email' => 'ama@example.test',
        ]);

        $identification = app(StudentIdentificationService::class)->save($student, [
            'identification_type' => 'ghana_card',
            'identifier' => 'GHA-123456789-0',
            'issuing_country' => 'gh',
            'mark_verified' => false,
        ]);

        $this->assertSame('GHA-123456789-0', $identification->identifier);
        $this->assertSame('•••••••••••89-0', $identification->masked_identifier);
        $this->assertNotSame('GHA-123456789-0', DB::table('student_identifications')->value('identifier'));
    }

    public function test_it_prevents_the_same_identity_number_from_being_assigned_twice(): void
    {
        $first = Student::create(['first_name' => 'Ama', 'last_name' => 'Mensah', 'email' => 'ama@example.test']);
        $second = Student::create(['first_name' => 'Esi', 'last_name' => 'Owusu', 'email' => 'esi@example.test']);

        $service = app(StudentIdentificationService::class);
        $service->save($first, ['identification_type' => 'ghana_card', 'identifier' => 'GHA-123456789-0']);

        $this->expectException(ValidationException::class);
        $service->save($second, ['identification_type' => 'ghana_card', 'identifier' => 'GHA-123456789-0']);
    }
}
