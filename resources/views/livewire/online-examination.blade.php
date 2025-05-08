<div class="container my-5">


  <div class="row">
      <!-- Main Exam Content -->
      <div class="mb-4 text-center">
          <h2>Course Title: {{ $exam->course->name }}</h2>
          Date of Exam: {{ $startedAt }}
          <p>Student Name:  {{ $student_name }} | Student ID : {{ $student_index }} </p>
          <p>Proctor: AI Sensei </p>
        
          
<div class="p-3 rounded border shadow-lg row bg-light">
<div class="col-md-12">

    <div class="card-body d-flex flex-column align-items-start justify-content-center">
       
        <div class="p-3 pt-4 card-text" style="font-size:18px;font-weight:600">
        <div class="d-flex justify-content-center">
            <h4 class="text-center text-danger"><strong> Instructions</strong></h4>
        </div>
            <p>
                You're being proctored by AI Sensei. Any suspecious activity will result in immediate disqualification. You're required to answer {{ count($questions) }} questions in total.
                <br>
                You have {{ $exam->duration }} minutes to complete this exam.
            </p>
            <livewire:exam-timer :startedAt="$timerStart" :completedAt="$timerFinish" :examSessionId="$examSession->id" />
        </div>
       
    
    </div>
</div>
    {{-- <div class="col-md-4">
        @livewire('proctoring-livewire', ['examId' => $exam->id, 'userId' => $user->id])
    </div> --}}
</div> <!-- row -->

      </div>

      <div class="row h-100">
        <!-- Main Exam Content -->
        <div class="col-md-9 d-flex flex-column">
            <div class="p-4 shadow-lg card question-card position-relative">
                <!-- Watermark -->
                <div class="watermark">
                    {{ $student_name }}
                </div>
    
                <div class="scrollable-questions flex-grow-1 scrollbar-container" id="questionsContainer">
                       
                    <form wire:submit.prevent="submitExam">
                        <div class="questions-container">
                            @foreach ($questions as $index => $question)
                                <div class="p-3 mb-4 question rounded-border">
                                    <p><strong>Q{{ $index + 1 }}:</strong> {{ $question['question'] }}</p>
                
                                    <ul class="list-unstyled">
                                        @foreach ($question['options'] as $option)
                                            <li>
                                                <label class="form-check-label">
                                                    <input type="radio" class="mx-2 form-check-input" name="responses[{{ $question['id'] }}]" 
                                                           value="{{ $option['id'] }}" 
                                                           wire:click="storeResponse({{ $question['id'] }}, {{ $option['id'] }})" 
                                                           @if (isset($responses[$question['id']]) && $responses[$question['id']] == $option['id']) checked @endif>
                                                    {{ $option['option_text'] }}
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>
    
        <div class="shadow-lg col-md-3 sidebar d-flex flex-column card question-card" style="height:550px">
            <div class="p-4 text-center">
                <h5>Questions Overview</h5>
                <p class="mb-0">
                    Questions Answered: <strong id="answeredCount">{{ count(array_filter($responses)) }}</strong> / {{ count($questions) }}
                </p>
            </div>
            <div id="questionsOverview" class="overflow-y-auto p-3 mb-2 flex-grow-1">
                <div class="flex-wrap gap-3 tracker-container d-flex justify-content-center">
                    @foreach ($questions as $index => $question)
                        <div 
                            class="tracker-item rounded-circle text-center 
                                   @if(isset($responses[$question['id']])) answered @else unanswered @endif"
                            style="width: 50px; height: 50px; line-height: 50px;"
                        >
                            {{ $index + 1 }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-white card-footer d-flex justify-content-center align-items-center">
                <button class="btn btn-primary w-100" wire:click="submitExam" id="submitBtn">Submit Exam</button>
            </div>
        </div>
        
    </div>
    
  </div>

 @include('components.partials.timer-scripts')
 @include('components.partials.styles.exam-styles')
 @include('components.partials.styles.scrollbar-styles')

</div> <!-- Root Container -->

