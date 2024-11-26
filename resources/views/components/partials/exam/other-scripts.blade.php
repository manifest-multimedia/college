// Sample questions data (you could have more than 100+ questions)
//   const questionsData = Array.from({ length: 100 }, (_, i) => ({
//     questionNumber: i + 1,
//     text: `What is the answer to question ${i + 1}?`,
//     options: ["Option A", "Option B", "Option C", "Option D"],
//     marks: Math.floor(Math.random() * 5) + 1,
//     answered: false
//   }));

 // Initialize questionsData with data from Livewire component
 const questionsData = @json($questions);

// Variables for pagination
const questionsPerPage = 15;
let currentPage = 0;

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

          console.log(questionsData);

          questionsToDisplay.forEach((q, idx) => {
              const questionDiv = document.createElement('div');
              questionDiv.className = "question-container";
              questionDiv.innerHTML = `
                  <h5>Question ${idx + 1} <small class="text-muted">[${q.marks} Mark(s)]</small></h5>
                  <p class="lead">${q.question}</p>
                  ${q.options.map((option, optionIdx) => `
                      <div class="form-check">
                          <input class="form-check-input" type="radio" name="answer${q.id}" id="q${q.id}option${optionIdx + 1}" value="${option}" onchange="markAnswered(${q.id})">
                          <label class="form-check-label" for="q${q.id}option${optionIdx + 1}">${option.option_text}</label>
                      </div>
                  `).join('')}
              `;
              questionsContainer.appendChild(questionDiv);

              const statusClass = q.answered ? 'answered' : 'not-answered';
              const questionBox = document.createElement('div');
              questionBox.className = statusClass;
              questionBox.textContent = idx + 1;
              questionBox.onclick = () => scrollToQuestion(q.id);
              questionsOverview.appendChild(questionBox);
          });
          answeredCount.textContent = questionsData.filter(q => q.answered).length;
          leftCount.textContent = questionsData.length - answeredCount.textContent;
      }

      renderQuestions(); // Call function to render questions initially


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