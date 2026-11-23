<?php
namespace Tests\Feature;
use Tests\TestCase;
class ExamSubmissionRedirectTest extends TestCase {
 public function test_exam_login_page_is_accessible_without_portal_authentication(): void {
  $this->get(route('take-exam'))->assertOk();
 }
 public function test_exam_submission_components_redirect_to_exam_login_not_portal_results(): void {
  foreach ([\App\Livewire\OnlineExamination::class, \App\Livewire\OnlineExaminationV2::class] as $class) {
   $source = file_get_contents((new \ReflectionClass($class))->getFileName());
   $this->assertStringContainsString("return redirect()->route('take-exam');", $source);
   $this->assertStringNotContainsString("redirect()->route('student.online-exam-results.show'", $source);
  }
 }
}
