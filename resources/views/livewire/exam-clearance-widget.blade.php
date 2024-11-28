<div class="container my-5">
    @if($mode=='index')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h1>Exam Clearance</h1>
                    </div>
                </div>
                <div class="card-body">
 <!-- Search Bar -->
 <input type="text" wire:model.live="search" class="mb-3 form-control" placeholder="Search by Student ID...">

 <!-- Table of Students -->
 <table class="table table-bordered">
     <thead>
         <tr>
             <th>Student ID</th>
             <th>Student Name</th>
             <th>Eligibility</th>
             <th>Action</th>
         </tr>
     </thead>
     <tbody>
         @forelse ($students as $student)
             <tr>
                 <td>{{ $student->student_id }}</td>
                 <td>{{ $student->student_name }}</td>
                 <td>
                     @if ($student->is_eligble)
                         <span class="badge bg-success">Eligible</span>
                     @else
                         <span class="badge bg-danger">Not Eligible</span>
                     @endif
                 </td>
                 <td>
                     <button wire:click="toggleEligibility({{ $student->id }})" class="btn btn-sm btn-warning">
                         Toggle Eligibility
                     </button>
                 </td>
             </tr>
         @empty
             <tr>
                 <td colspan="4" class="text-center">No students found</td>
             </tr>
         @endforelse
     </tbody>
 </table>

 <!-- Pagination -->
 {{ $students->links() }}
                </div>
            </div>
           
        </div>

        
    </div>
    @endif
    @if($mode=='add')
    <div class="row">
        <!-- Add Student Form -->
        <div class="mt-4 col-md-6">
            <h5>Add Student</h5>
            <form wire:submit.prevent="addStudent">
                <div class="mb-3">
                    <label for="student_id" class="form-label">Student ID</label>
                    <input type="text" wire:model="student_id" id="student_id" class="form-control @error('student_id') is-invalid @enderror">
                    @error('student_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="student_name" class="form-label">Student Name</label>
                    <input type="text" wire:model="student_name" id="student_name" class="form-control @error('student_name') is-invalid @enderror">
                    @error('student_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-check">
                    <input type="checkbox" wire:model="is_eligble" id="is_eligble" class="form-check-input">
                    <label for="is_eligble" class="form-check-label">Is Eligible?</label>
                </div>

                <button type="submit" class="mt-3 btn btn-primary">Add Student</button>
            </form>

            @if (session()->has('message'))
                <div class="mt-3 alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
