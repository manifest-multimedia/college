<?php

/*
Contains functions for generating reports

*/

use App\Exports\ExportToExcel;
use App\Models\Student;

if (! function_exists('generateReport')) {
    /*************  ✨ Codeium Command ⭐  *************/
    /**
     * Generates a report based on the provided request
     *
     * @param  Request  $request  The request object containing the report data
     * @return array The generated report
     */
    /******  8dc1a823-a575-4a27-9322-6e0f24b982d2  *******/
    function generateReport($type, $class = null)
    {
        switch ($type) {
            case 'Student Report':
                $data = Student::where('cohort_id', $class)->get();
                $fields = getColumns($type);
            default:
                return [];
        }
    }
}

if (! function_exists('filterData')) {

    function filterData($data)
    {
        if (count($data) > 0) {
            try {
                if (is_array($data)) {
                    // Flip selected fields for better comparison
                    $flippedSelectedFields = array_flip($fieldNames);
                    // dd('Flipped Selected Fields:', $flippedSelectedFields, 'Original Data Sample:', $data[0]->toArray()); // Adjusted for sample item

                    // Map over data to filter out non-matching fields
                    $data = array_map(function ($item) use ($fieldNames) {
                        $itemArray = $item->toArray();

                        // Intersect item keys with selected fields, keeping only matching fields
                        return array_intersect_key($itemArray, array_flip($fieldNames));
                    }, $data);
                } elseif ($data instanceof \Illuminate\Support\Collection) {
                    // Flip selected fields for better comparison
                    $flippedSelectedFields = array_flip($fieldNames);
                    // dd('Flipped Selected Fields:', $flippedSelectedFields, 'Original Data Sample:', $data->first()->toArray()); // Adjusted for sample item

                    // Map over collection to filter out non-matching fields
                    $data = $data->map(function ($item) use ($fieldNames) {
                        $itemArray = $item->toArray();

                        // Intersect item keys with selected fields, keeping only matching fields
                        return array_intersect_key($itemArray, array_flip($fieldNames));
                    });
                } else {
                    throw new \Exception('Invalid data type. Expected array or collection.');
                }

                // Update selected fields to include only matched fields after filtering
                $matchedFields = array_keys($data[0]); // Assumes data is not empty after filtering
                // dd($matchedFields);
                $selectedFields = $matchedFields;

                return Excel::download(new ExportToExcel($data, $fieldNames, $columnNames), 'admission-report.xlsx');
            } catch (\Exception $e) {
                // Log exception details
                Log::info('message: '.$e->getMessage().', file: '.$e->getFile().', line: '.$e->getLine());
            }
        }
    }
}

if (! function_exists('getColumns')) {
    function getColumns($reportOption)
    {

        switch ($reportOption) {
            case 'Student Report':

                $columns = [
                    'id' => 'ID',
                    'parent_id' => 'Parent',
                    'admission_no' => 'Admission Number',
                    'roll_no' => 'Roll Number',
                    'admission_date' => 'Admission Date',
                    'firstname' => 'First Name',
                    'middlename' => 'Middle Name',
                    'lastname' => 'Last Name',
                    'mobileno' => 'Mobile Number',
                    'email' => 'Email',
                    'state' => 'State',
                    'city' => 'City',
                    'pincode' => 'Pincode',
                    'religion' => 'Religion',
                    'dob' => 'Date of Birth',
                    'gender' => 'Gender',
                    'current_address' => 'Current Address',
                    'permanent_address' => 'Permanent Address',
                    'send' => 'Send',
                    'route_id' => 'Route ID',
                    'school_house_id' => 'School House',
                    'blood_group' => 'Blood Group',
                    'pickup_point_id' => 'Pickup Point',
                    'hostel_room_id' => 'Hostel Room',
                    'hostel_id' => 'Hostel',
                    'bank_account_no' => 'Bank Account Number',
                    'bank_name' => 'Bank Name',
                    'ifsc_code' => 'IFSC Code',
                    'father_name' => 'Father\'s Name',
                    'father_phone' => 'Father\'s Phone',
                    'mother_occupation' => 'Mother\'s Occupation',
                    'guardian_name' => 'Guardian\'s Name',
                    'guardian_relation' => 'Guardian\'s Relation',
                    'guardian_phone' => 'Guardian\'s Phone',
                    'guardian_occupation' => 'Guardian\'s Occupation',
                    'guardian_email' => 'Guardian\'s Email',
                    'is_active' => 'Is Active',
                    'previous_school' => 'Previous School',
                    'height' => 'Height',
                    'measurement_date' => 'Measurement Date',
                    'disable_reason' => 'Disable Reason',
                    'note' => 'Note',
                    'disable_note' => 'Disable Note',
                    'class_id' => 'Class',
                    'section_id' => 'Section',
                    'ethnicity' => 'Ethnicity',
                    'weight' => 'Weight',
                    'medical_history' => 'Medical History',
                ];
                break;
            case 'Student Gender Ratio':
                $columns = ['class_id', 'section_id', 'gender'];
                break;
            case 'Guardian Report':
                $columns = ['id', 'first_name', 'last_name', 'relation', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Payroll Report':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Fees Balance Report':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Student Attendance Report':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Staff Report':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Lesson Plan Report':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Student Behavior Report':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Inventory Report':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Transport Report':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Fees Statement for Student':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Fees Collection Report':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            case 'Fees Balance Statement':
                $columns = ['id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'class', 'section', 'created_at'];
                break;
            default:
                $columns = []; // Handle unknown report types
                break;
        }

        return $columns;
    }
}
