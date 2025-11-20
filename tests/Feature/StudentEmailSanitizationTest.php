<?php

namespace Tests\Feature;

use App\Imports\StudentImporter;
use Tests\TestCase;

class StudentEmailSanitizationTest extends TestCase
{
    /** @test */
    public function it_does_not_validate_email_format_in_rules_method()
    {
        $importer = new StudentImporter(1, 1);
        $rules = $importer->rules();

        // Email validation should NOT be in the rules array
        $this->assertArrayNotHasKey('*.email', $rules);

        // Only student_id validation should remain
        $this->assertArrayHasKey('*.student_id', $rules);
        $this->assertEquals('nullable|string', $rules['*.student_id']);
    }

    /** @test */
    public function it_sanitizes_email_spaces_in_map_row_method()
    {
        $importer = new StudentImporter(1, 1, ['email' => 'email']);

        // Use reflection to access the private method
        $reflection = new \ReflectionClass($importer);
        $method = $reflection->getMethod('mapRowToStudentData');
        $method->setAccessible(true);

        // Create a mock row with email containing spaces
        $row = collect([
            'email' => 'john.doe@example.com ', // Trailing space
        ]);

        // Test the sanitization
        $result = $method->invoke($importer, $row);

        // Assert the email was sanitized
        $this->assertEquals('john.doe@example.com', $result['email']);
        $this->assertTrue(filter_var($result['email'], FILTER_VALIDATE_EMAIL) !== false);
    }
}
