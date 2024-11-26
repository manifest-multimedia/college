{{-- Because she competes with no one, no one can compete with her. --}}
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="p-4 shadow-lg card" style="max-width: 400px; width: 100%;">
        <h3 class="mb-4 text-center">Exam Login</h3>
{{-- @if(session('message'))
<div class="alert alert-success">
    <span>{{ session('message') }}</span>
</div>
@endif  --}}
       
  @if($errors->any() || session()->has('error') || session()->has('message'))
        <div class="alert {{ $errors->any() || session()->has('error') ? 'alert-danger' : 'alert-success' }}">
            @if($errors->any())
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @else
                {{ session('error') ?? session('message') }}
            @endif
        </div>
    @endif
     
        <form wire:submit.prevent="startExam">

          @csrf
          <div class="mb-3">
            <label for="studentID" class="form-label">Student ID</label>
            <input type="text" class="form-control" id="studentID" placeholder="Enter your Student ID" required wire:model="studentId">
        </div>
        <div class="mb-3">
            <label for="examPassword" class="form-label">Exam Password</label>
            <input type="text" class="form-control" id="examPassword" placeholder="Enter your Exam Password" required wire:model="examPassword">
        </div>
            <button class="btn btn-primary w-100" type="submit">Start Exam</button>
        </form>
    </div>
</div>