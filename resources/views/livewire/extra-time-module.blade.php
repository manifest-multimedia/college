<div>
    {{-- Do your work, then step back. --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">
            <h3>Extra Time Module</h3>
        </div>
        <div class="card-actions">
            {{-- Filter By Exam --}}
            <input type="text" wire:model.live="exam_filter" class="form-control" placeholder="Filter by Exam">
        </div>
    </div>
    <div class="card-body">
<div class="border-collapse table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Student</th>
                <th>Exam Session</th>
                <th>Exam Duration</th>
                <th>Extra Time</th> 
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
           @foreach ($students as $student)
                      
               <tr>
                   <td> 
                       <div class="d-flex align-items-start flex-column">
                           <div class="flex-row d-flex me-2">
                               <strong>
                               {{ $student->user ? $student->user->name : '' }}
                               </strong>
                           </div>
                       <div class="flex-row d-flex"><small>{{ $student->student_id }}</small></div>
                       <div class="flex-row d-flex">
                           <small>{{ $student->email }}</small>
                       </div>
                       </div>
                   </td>
                   <td>
                       @if($student->user && $student->user->examSessions)
                            @foreach ($student->user->examSessions as $item)
                                {{ $item->exam->course->name }}
                            @endforeach
                            @else 
                            <span class="text-danger">No Exam Session</span>
                       @endif
                   </td>
                   <td>
                       @if($student->user && $student->user->examSessions)
                            @foreach ($student->user->examSessions as $item)
                                {{ $item->exam->duration }}
                            @endforeach
                            @else 
                            <span class="text-danger">No Exam Session</span>
                       @endif
                   </td>
                   <td>
                    <div class="flex-row gap-2 d-flex align-items-center justify-content-center">
                        <div class="div">

                            <input type="text" wire:model="extraTime.{{ $student->id }}" class="form-control" placeholder="Extra Time">
                        </div>
                        <div class="div">

                            <button class="btn btn-primary" wire:click="addMinutes({{ $student->id }})">Add Minutes</button>
                        </div>
                    </div>


                   </td>
                   <td>
                       <button class="btn btn-primary" wire:click="viewDetails({{ $student->id }})">View</button>
                       
                   </td>
               </tr>
                       
           @endforeach
        </tbody>
    </table>
</div>
       
    </div>
    <div class="card-footer">

    </div>
</div>
   


</div>
