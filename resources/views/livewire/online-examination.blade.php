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

      <div class="col-md-9">
        <!-- Scrollable Questions Container -->
        <div class="p-4 shadow-lg card question-card position-relative">
            <!-- Watermark -->
            <div class="watermark">
                {{ $student_name }}
            </div>
    
            <div class="scrollable-questions" id="questionsContainer">
                <form id="examForm">
                    @foreach($questions as $question)
                        <div class="question-container">
                            <p>{{ $question['question'] }}</p>
                            @foreach($question['options'] as $option)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="question{{ $question['id'] }}" value="{{ $option['id'] }}"
                                           wire:click="storeResponse({{ $question['id'] }}, {{ $option['id'] }})">
                                    <label class="form-check-label">
                                        {{ $option['option_text'] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </form>
            </div>
        </div>
    </div>
    
      <div class="col-md-3">
          <h5>Questions Overview</h5>
          <div id="questionsOverview">
              <!-- Dynamic question status will appear here -->
          </div>
          <div id="questionCounts">
              {{-- <p>Answered: {{ count($answeredQuestions) }}</p> --}}
              <p>Total Questions: {{ count($questions) }}</p>
          </div>
          <button class="btn btn-primary w-100" wire:click="submitExam" id="submitBtn">Submit Exam</button>
      </div>
  </div>

  <style>
    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg); /* Rotate watermark diagonally */
        font-size: 3rem; /* Adjust size as needed */
        font-weight: 900;
        color: rgba(0, 0, 0, 0.050); /* Transparent black */
        white-space: nowrap;
        text-align: center;
        z-index: 0;
        pointer-events: none; /* Make it unclickable */
        user-select: none; /* Prevent text selection */
    }

    .scrollable-questions {
        position: relative;
        z-index: 1; /* Ensure questions are on top of the watermark */
        max-height: 500px; /* Adjust height as needed */
        overflow-y: auto; /* Enable scrolling */
        padding: 10px;
    }

    .question-card {
        position: relative;
    }
</style>
 @include('components.partials.timer-scripts')

</div>
