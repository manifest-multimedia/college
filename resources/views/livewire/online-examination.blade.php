<div class="container my-5">
  <style>
      .timer {
          font-size: 1.25rem;
          color: #d9534f;
      }
      .question-card {
          max-width: 800px;
          margin: auto;
      }
      .scrollable-questions {
          max-height: 70vh;
          overflow-y: auto;
          padding: 10px;
      }
      .question-container {
          border: 1px solid #ddd;
          border-radius: 8px;
          padding: 15px;
          margin-bottom: 20px;
      }
      .answered {
          background-color: #28a745;
          color: white;
          border-radius: 50%;
          width: 30px;
          height: 30px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          margin: 5px;
          cursor: pointer;
      }
      .not-answered {
          background-color: #6c757d;
          color: white;
          border-radius: 50%;
          width: 30px;
          height: 30px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          margin: 5px;
          cursor: pointer;
      }
  </style>

  <div class="row">
      <!-- Main Exam Content -->
      <div class="mb-4 text-center">
          <h2>Course Title: {{ $exam->course->name }}</h2>
          <p>Paper Duration: {{ $exam->duration }} minutes</p>
          <p>Student Name:  {{ $student_name }}</p>
          <p class="timer" id="countdown">Time Left: <span id="timeLeft">{{ gmdate('H:i:s', $remainingTime) }}</span></p>
      </div>

      <div class="col-md-9">
          <!-- Scrollable Questions Container -->
          <div class="p-4 shadow-lg card question-card">
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

              {{-- <div class="mt-4 d-flex justify-content-between">
                  <button class="btn btn-secondary" id="prevBtn" disabled>Previous</button>
                  <button class="btn btn-primary" id="nextBtn" wire:click="submitExam">Next</button>
              </div> --}}
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

  <script>
      let timerInterval = setInterval(function () {
          @this.call('countdown');
          let remainingTime = @this.get('remainingTime');
          let hours = Math.floor(remainingTime / 3600);
          let minutes = Math.floor((remainingTime % 3600) / 60);
          let seconds = remainingTime % 60;
          document.getElementById('timeLeft').innerText = `${hours}:${minutes}:${seconds}`;
      }, 1000);
  </script>
</div>
