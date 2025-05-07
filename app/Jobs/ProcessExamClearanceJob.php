<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Models\OfflineExam;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Notifications\ExamClearanceNotification;
use App\Services\Exams\ExamClearanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessExamClearanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The exam to process clearance for.
     *
     * @var Exam|OfflineExam
     */
    protected $exam;
    
    /**
     * The students to process.
     *
     * @var Collection|array|null
     */
    protected $students;
    
    /**
     * The academic year to use for processing.
     *
     * @var AcademicYear|null
     */
    protected $academicYear;
    
    /**
     * The semester to use for processing.
     *
     * @var Semester|null
     */
    protected $semester;
    
    /**
     * Whether to notify students of their clearance status.
     *
     * @var bool
     */
    protected $shouldNotify;

    /**
     * Create a new job instance.
     *
     * @param Exam|OfflineExam $exam The exam to process clearance for
     * @param Collection|array|null $students Specific students to process, or null for all students
     * @param AcademicYear|null $academicYear The academic year context
     * @param Semester|null $semester The semester context
     * @param bool $shouldNotify Whether to notify students of their clearance status
     */
    public function __construct(
        Model $exam, 
        $students = null, 
        ?AcademicYear $academicYear = null,
        ?Semester $semester = null,
        bool $shouldNotify = true
    ) {
        $this->exam = $exam;
        $this->students = $students;
        $this->academicYear = $academicYear;
        $this->semester = $semester;
        $this->shouldNotify = $shouldNotify;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing exam clearance for ' . get_class($this->exam) . ' #' . $this->exam->id);
        
        try {
            // Get the clearance service
            $clearanceService = new ExamClearanceService();
            
            // Get the academic year and semester if not provided
            if (!$this->academicYear) {
                $this->academicYear = AcademicYear::where('is_current', true)->first();
            }
            
            if (!$this->semester) {
                $this->semester = Semester::where('is_current', true)->first();
            }
            
            // Process all students if none specified
            if (!$this->students) {
                $this->students = Student::whereHas('user', function ($query) {
                    $query->where('active', true);
                })->get();
            } elseif (is_array($this->students) && is_numeric($this->students[0])) {
                // If an array of IDs is provided, fetch the students
                $this->students = Student::whereIn('id', $this->students)->get();
            }
            
            // Process clearance for each student
            $clearances = $clearanceService->processBulkClearance(
                $this->students,
                $this->exam,
                $this->academicYear,
                $this->semester
            );
            
            // Send notifications if enabled
            if ($this->shouldNotify) {
                $this->sendClearanceNotifications($clearances);
            }
            
            Log::info('Completed exam clearance processing for ' . count($clearances) . ' students');
        } catch (\Exception $e) {
            Log::error('Error processing exam clearance: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send notifications to students about their clearance status.
     *
     * @param array $clearances
     * @return void
     */
    protected function sendClearanceNotifications(array $clearances): void
    {
        // Process each clearance and send notifications
        foreach ($clearances as $clearance) {
            try {
                $student = $clearance->student;
                $user = $student->user;
                
                if (!$user) {
                    Log::warning('Student #' . $student->id . ' has no associated user account. Skipping notification.');
                    continue;
                }
                
                // Send the notification using the Communication Module
                $user->notify(new ExamClearanceNotification($clearance));
                
                Log::info('Sent exam clearance notification to student #' . $student->id);
            } catch (\Exception $e) {
                Log::error('Error sending clearance notification: ' . $e->getMessage());
            }
        }
    }
}
