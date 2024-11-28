@include('components.partials.timer-styles')

  <div class="row">
      <!-- Main Exam Content -->
      <div class="mb-4 text-center">
          <h2>Course Title: {{ $exam->course->name }}</h2>
          <p>Paper Duration: {{ $exam->duration }} minutes</p>
          <p>Student Name:  {{ $student_name }} | Student ID : {{ $student_index }}</p>
        
          <div>
            <h4 class="text-danger">
                <span id="countdown" class="badge bg-danger pulse">
                    <strong>Time Left </strong> <span id="remaining-time">00:00:00</span>
                </span>
            </h4>
        </div>
      </div>

      <div class="row h-100">
        <!-- Main Exam Content -->
        <div class="col-md-9 d-flex flex-column">
            <div class="p-4 shadow-lg card question-card position-relative h-100">
                <!-- Watermark -->
                <div class="watermark">
                    {{ $student_name }}
                </div>
    
                <div class="scrollable-questions flex-grow-1" id="questionsContainer">
                    <form wire:submit.prevent="submitExam">
                        <div class="questions-container">
                            @foreach ($questions as $index => $question)
                                <div class="p-3 mb-4 question rounded-border">
                                    <p><strong>Q{{ $index + 1 }}:</strong> {{ $question['question'] }}</p>
                
                                    <ul>
                                        @foreach ($question['options'] as $option)
                                            <li>
                                                <label>
                                                    <input type="radio" name="responses[{{ $question['id'] }}]" 
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
    
        <div class="col-md-3 sidebar h-100 d-flex flex-column">
            <div>
                <h5>Questions Overview</h5>
                <p>Questions Answered: <strong id="answeredCount">{{ count(array_filter($responses)) }}</strong> / {{ count($questions) }}</p>
            </div>
            <div id="questionsOverview" class="overflow-y-auto flex-grow-1">
                <div class="question-tracker h-100 d-flex flex-column">
                    
                    <div class="flex-wrap tracker-container d-flex align-items-center justify-content-between h-100">
                        @foreach ($questions as $index => $question)
                            <div class="tracker-item rounded-circle text-center 
                                        @if(isset($responses[$question['id']])) answered @else unanswered @endif">
                                {{ $index + 1 }}
                            </div>
                        @endforeach
                    </div>
                </div>
                
            </div>
            <button class="mt-3 btn btn-primary w-100" wire:click="submitExam" id="submitBtn">Submit Exam</button>
        </div>
    </div>
    
  </div>

 @include('components.partials.timer-scripts')
 
</div>

