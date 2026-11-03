<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentSearchOrderTest extends TestCase
{
    use RefreshDatabase;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = Student::create([
            'student_id' => 'STU2026001',
            'first_name' => 'Gloria',
            'other_name' => 'Sarfo',
            'last_name' => 'Owusu',
            'email' => 'gloria.owusu@example.com',
            'mobile_number' => '0545475118',
            'status' => 'active',
        ]);
    }

    public function test_finds_student_with_full_name_in_standard_order(): void
    {
        $results = Student::search('Gloria Sarfo Owusu')->get();
        $this->assertTrue($results->contains('id', $this->student->id));
    }

    public function test_finds_student_with_surname_first_order(): void
    {
        // The exact problem reported by the user: Owusu Gloria Sarfo
        $results = Student::search('Owusu Gloria Sarfo')->get();
        $this->assertTrue($results->contains('id', $this->student->id));
    }

    public function test_finds_student_with_partial_name_permutations(): void
    {
        // Gloria
        $results = Student::search('Gloria')->get();
        $this->assertTrue($results->contains('id', $this->student->id));

        // Gloria Sarfo
        $results = Student::search('Gloria Sarfo')->get();
        $this->assertTrue($results->contains('id', $this->student->id));

        // Owusu Sarfo
        $results = Student::search('Owusu Sarfo')->get();
        $this->assertTrue($results->contains('id', $this->student->id));

        // Sarfo Owusu
        $results = Student::search('Sarfo Owusu')->get();
        $this->assertTrue($results->contains('id', $this->student->id));

        // Owusu Gloria
        $results = Student::search('Owusu Gloria')->get();
        $this->assertTrue($results->contains('id', $this->student->id));
    }

    public function test_finds_student_by_student_id_email_and_phone(): void
    {
        // By ID
        $byId = Student::search('STU2026001')->get();
        $this->assertTrue($byId->contains('id', $this->student->id));

        // By Email
        $byEmail = Student::search('gloria.owusu@example.com')->get();
        $this->assertTrue($byEmail->contains('id', $this->student->id));

        // By Phone
        $byPhone = Student::search('0545475118')->get();
        $this->assertTrue($byPhone->contains('id', $this->student->id));
    }

    public function test_empty_search_returns_unfiltered_query(): void
    {
        $all = Student::search('')->get();
        $this->assertCount(1, $all);

        $nullSearch = Student::search(null)->get();
        $this->assertCount(1, $nullSearch);
    }
}
