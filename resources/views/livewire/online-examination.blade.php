
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
      <h2>Course Title: Advanced Mathematics</h2>
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
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="warningModalLabel">Warning</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          You attempted navigating away from the exam screen. You'll be locked out of the exam after 3 attempts.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>


  

<!-- Bootstrap JS and Countdown Timer Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Countdown Timer
  let countdownElement = document.getElementById('countdown');
  let remainingTime = 30 * 60; // 30 minutes in seconds

  function startCountdown() {
    setInterval(() => {
      if (remainingTime <= 0) {
        countdownElement.innerHTML = "Time's Up!";
        return;
      }
      remainingTime--;
      let minutes = Math.floor(remainingTime / 60);
      let seconds = remainingTime % 60;
      countdownElement.innerHTML = `Time Left: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }, 1000);
  }
  startCountdown();

  // Sample questions data (you could have more than 100+ questions)
  const questionsData = Array.from({ length: 100 }, (_, i) => ({
    questionNumber: i + 1,
    text: `What is the answer to question ${i + 1}?`,
    options: ["Option A", "Option B", "Option C", "Option D"],
    marks: Math.floor(Math.random() * 5) + 1,
    answered: false
  }));

  // Variables for pagination
  const questionsPerPage = 15;
  let currentPage = 0;

  // Function to render questions
  function renderQuestions() {
    const questionsContainer = document.getElementById('questionsContainer');
    const questionsOverview = document.getElementById('questionsOverview');
    const answeredCount = document.getElementById('answeredCount');
    const leftCount = document.getElementById('leftCount');

    questionsContainer.innerHTML = ""; // Clear previous questions
    questionsOverview.innerHTML = ""; // Clear previous question status

    const start = currentPage * questionsPerPage;
    const end = start + questionsPerPage;
    const questionsToDisplay = questionsData.slice(start, end);

    questionsToDisplay.forEach((q, idx) => {
      // Render question in main content
      const questionDiv = document.createElement('div');
      questionDiv.className = "question-container";
      questionDiv.innerHTML = `
        <h5>Question ${q.questionNumber} <small class="text-muted">(${q.marks} Marks)</small></h5>
        <p class="lead">${q.text}</p>
        ${q.options.map((option, optionIdx) => `
          <div class="form-check">
            <input class="form-check-input" type="radio" name="answer${q.questionNumber}" id="q${q.questionNumber}option${optionIdx + 1}" value="${option}" onchange="markAnswered(${q.questionNumber})">
            <label class="form-check-label" for="q${q.questionNumber}option${optionIdx + 1}">${option}</label>
          </div>
        `).join('')}
      `;
      questionsContainer.appendChild(questionDiv);

      // Render question number in sidebar (answered status)
      const statusClass = q.answered ? 'answered' : 'not-answered';
      const questionBox = document.createElement('div');
      questionBox.className = statusClass;
      questionBox.textContent = q.questionNumber;
      questionBox.onclick = () => scrollToQuestion(q.questionNumber);
      questionsOverview.appendChild(questionBox);
    });

    // Update answered/left counts
    const answeredQuestions = questionsData.filter(q => q.answered).length;
    answeredCount.textContent = answeredQuestions;
    leftCount.textContent = questionsData.length - answeredQuestions;

    // Show the submit button only if it's the last page of questions
    const isLastPage = end >= questionsData.length;
    const submitButton = document.getElementById('submitBtn');
    if (isLastPage && !submitButton) {
      const submitBtn = document.createElement('button');
      submitBtn.type = 'submit';
      submitBtn.className = 'btn btn-success mt-4';
      submitBtn.id = 'submitBtn';
      submitBtn.innerText = 'Submit Exam';
      questionsContainer.appendChild(submitBtn);
    } else if (!isLastPage && submitButton) {
      submitButton.remove();
    }

    // Update pagination button states
    document.getElementById('prevBtn').disabled = currentPage === 0;
    document.getElementById('nextBtn').disabled = isLastPage;
  }

  // Function to mark a question as answered
  function markAnswered(questionNumber) {
    const question = questionsData.find(q => q.questionNumber === questionNumber);
    if (question) {
      question.answered = true;
      renderQuestions();
    }
  }

  // Scroll to a specific question when clicked in the sidebar
  function scrollToQuestion(questionNumber) {
    const questionElement = document.getElementById(`q${questionNumber}`);
    if (questionElement) {
      questionElement.scrollIntoView({ behavior: 'smooth' });
    }
  }

  // Event listeners for pagination buttons
  document.getElementById('prevBtn').addEventListener('click', () => {
    if (currentPage > 0) {
      currentPage--;
      renderQuestions();
    }
  });

  document.getElementById('nextBtn').addEventListener('click', () => {
    if ((currentPage + 1) * questionsPerPage < questionsData.length) {
      currentPage++;
      renderQuestions();
    }
  });

  // Initial render of questions
  renderQuestions();


//   Navigating away
 // Counter to track the number of navigation attempts
  // Counter to track the number of navigation attempts
let attemptCount = 0;
const maxAttempts = 3;

// Modal reference
const warningModal = new bootstrap.Modal(document.getElementById('warningModal'));

// Function to show the warning modal
function showWarning() {
  warningModal.show();
}

// Function to handle strike count and warning display
function handleStrike() {
  if (attemptCount < maxAttempts - 1) {
    attemptCount++;
    showWarning();
  } else {
    alert('You have attempted to navigate away from the exam too many times. You have been disqualified from this exam.');
    // Auto-submit logic can go here
  }
}

// Set a flag in sessionStorage to detect reloads
sessionStorage.setItem('pageReloaded', 'true');

// Event listener for visibility changes (detects tab switching)
document.addEventListener('visibilitychange', function() {
  if (document.visibilityState === 'hidden') {
    handleStrike();
  }
});

// Event listener for key combinations (new tab or window)
document.addEventListener('keydown', function(event) {
  if ((event.ctrlKey && event.key === 't') || // Ctrl+T for new tab
      (event.ctrlKey && event.key === 'n') || // Ctrl+N for new window
      (event.ctrlKey && event.shiftKey && event.key === 'n') // Ctrl+Shift+N for incognito
  ) {
    event.preventDefault();
    handleStrike();
  }
});

// Event listener to detect when the user tries to leave the page
window.addEventListener('beforeunload', function (event) {
  // Check if page reload is permitted (detecting a reload vs. navigation attempt)
  const isReload = sessionStorage.getItem('pageReloaded') === 'true';
  if (isReload) {
    // Clear the reload flag after handling
    sessionStorage.removeItem('pageReloaded');
  } else {
    // Count the attempt if it's not a page reload
    if (attemptCount < maxAttempts) {
      handleStrike();
      event.preventDefault(); // Prevent navigation
      event.returnValue = ''; // This is required for Chrome
    }
  }
});

// Reset the reload flag on page load
window.addEventListener('load', function() {
  sessionStorage.removeItem('pageReloaded');
});


</script>
