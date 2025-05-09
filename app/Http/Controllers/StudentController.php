<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Delete the specified student from the database.
     *
     * @param  int  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy($student)
    {
        try {
            DB::beginTransaction();
            
            // Find the student
            $studentModel = Student::findOrFail($student);
            
            // Store student details for logging
            $studentName = $studentModel->first_name . ' ' . $studentModel->last_name;
            $studentId = $studentModel->student_id;
            
            // Delete the student
            $studentModel->delete();
            
            // Log the deletion
            Log::info('Student deleted', [
                'student_id' => $studentId,
                'name' => $studentName,
                'deleted_by' => auth()->user()->name,
                'deleted_by_id' => auth()->id()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Student {$studentName} (ID: {$studentId}) has been successfully deleted."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting student', [
                'student_id' => $student,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student: ' . $e->getMessage()
            ], 500);
        }
    }
}
