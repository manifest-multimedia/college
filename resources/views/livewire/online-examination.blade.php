
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
      .aside {
        position: sticky;
        top: 0;
        padding: 15px;
        background-color: #f8f9fa;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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
  <!-- Exam Header -->
  <div class="mb-4 text-center">
    <h2> Course Title: {{ $exam->course->name ? $exam->course->name : '' }}</h2>
    <p>Paper Duration: {{ $exam->duration ? $exam->duration : '' }} minutes</p>
    <p class="timer" id="countdown">Time Left: 00:30:00</p>
  </div>
  <div class="col-md-9">

    <!-- Scrollable Questions Container -->
    <div class="p-4 shadow-lg card question-card">
      <div class="scrollable-questions" id="questionsContainer">
        <!-- Form for Questions -->
        <form id="examForm">
          <!-- JavaScript will dynamically insert question blocks here -->
        </form>
      </div>

      <!-- Pagination for Questions -->
      <div class="mt-4 d-flex justify-content-between">
        <button class="btn btn-secondary" id="prevBtn" disabled>Previous</button>
        <button class="btn btn-primary" id="nextBtn">Next</button>
      </div>
    </div>
  </div>

  <!-- Sidebar Aside Component -->
  <div class="col-md-3 aside">
    <h5>Questions Overview</h5>
    <div id="questionsOverview">
      <!-- JavaScript will dynamically insert question status here -->
    </div>
    <hr>
    <div id="questionCounts">
      <p>Answered: <span id="answeredCount">0</span></p>
      <p>Left: <span id="leftCount">100</span></p>
    </div>
    <hr>
    
    <button class="btn btn-primary w-100" id="submitBtn">Submit Exam</button>

  </div>
</div>
<!-- Modal for Navigation Warning -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true"


data-bs-backdrop="static"
>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger" id="warningModalLabel" >Warning  <span id="strikeCount" class="text-danger"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        You attempted navigating away from the exam screen. You'll be locked out of the exam after 3 attempts.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" >Close</button>
      </div>
    </div>
  </div>
</div>
</div>


<!-- Bootstrap JS and Countdown Timer Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<x-partials.exam-scripts />