<div class="container my-5">
  <style>

.pulse {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}
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
          <p>Student Name:  {{ $student_name }} | Student ID={{ $student_index }}</p>
          {{-- <p class="timer" id="countdown">Time Left: <span id="timeLeft">{{ gmdate('H:i:s', $remainingTime) }}</span></p> --}}
          {{-- <div wire:poll.10s="getRemainingTime" >
            <h4 class="text-danger">
                
                <span class="badge bg-danger pulse">
                    <strong>Time Left </strong>   {{ $remainingTime }}
                </span>
            </h4>
        </div> --}}
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
    document.addEventListener('DOMContentLoaded', () => {
        let remainingSeconds = Math.floor(@json($remainingTime)); // Ensure whole seconds
        const countdownElement = document.getElementById('remaining-time');

        // Function to format time as HH:MM:SS
        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        // Update the countdown every second
        const interval = setInterval(() => {
            if (remainingSeconds <= 0) {
                countdownElement.textContent = 'Time is up!';
                clearInterval(interval);
                return;
            }

            // Decrease remaining time and update display
            remainingSeconds -= 1;
            countdownElement.textContent = formatTime(remainingSeconds);
        }, 1000);

        // Periodically fetch updated remaining time from the server
        setInterval(() => {
            @this.call('getRemainingTime').then(serverTime => {
                const serverSeconds = Math.floor(serverTime); // Ensure whole seconds
                // Synchronize only if there's significant drift
                if (Math.abs(serverSeconds - remainingSeconds) > 2) {
                    remainingSeconds = serverSeconds;
                }
            });
        }, 10000); // Fetch every 10 seconds
    });
</script>

</div>
