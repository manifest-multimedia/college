<style>

/* Add rounded bounding box around each question */
.rounded-border {
border: 1px solid #ddd;
border-radius: 10px;
background-color: #f8f9fa;
padding: 15px;
}
.question-card {
  position: relative;
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
    .tracker-container {
    max-width: 100%; /* Ensure it doesn't exceed the container width */
}
/* .tracker-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: center;
        } */

.tracker-item {
    font-size: 14px;
    font-weight: bold;
    background-color: #f8f9fa; /* Light gray for unanswered */
    color: #6c757d; /* Muted text for unanswered */
    display: inline-flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    border: 1px solid #ced4da; /* Border for better distinction */
    transition: all 0.3s ease-in-out;
}

.tracker-item.answered {
    background-color:#28a745; /* Blue for answered */
    color: #fff; /* White text */
    border-color:#28a745;
}

.tracker-item:hover {
    transform: scale(1.1); /* Slightly enlarge on hover */
}
.scrollable-questions {
          position: relative;
          z-index: 1; /* Ensure questions are on top of the watermark */
          max-height: 500px; /* Adjust height as needed */
          overflow-y: auto; /* Enable scrolling */
          padding: 10px;
      }
  

/* Paper Water Mark Styles */
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
          z-index: 3;
          pointer-events: none; /* Make it unclickable */
          user-select: none; /* Prevent text selection */
      }
  
</style>
    