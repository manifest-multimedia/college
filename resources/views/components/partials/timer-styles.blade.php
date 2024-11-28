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
          z-index: 2;
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
      
        /* Tracker Styles */
        .question-tracker {
            max-width: 300px;
            text-align: center;
        }
        .tracker-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: center;
        }
        .tracker-item {
            width: 30px;
            height: 30px;
            line-height: 30px;
            font-size: 14px;
            color: white;
            background-color: grey;
            border-radius: 50%;
            display: inline-block;
        }
        .tracker-item.answered {
            background-color: blue;
        }
        .tracker-item.unanswered {
            background-color: lightgrey;
        }
        .tracker-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px; /* Adds spacing between items */
    justify-content: space-between; /* Evenly distribute tracker items */
    padding: 10px; /* Adds internal spacing */
}

/* Tracker items styling */
.tracker-item {
    width: 40px; /* Adjust size */
    height: 40px;
    line-height: 40px;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: white;
    background-color: lightgrey; /* Default for unanswered */
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease; /* Smooth transitions for dynamic updates */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    cursor: pointer; /* Adds hover interactivity */
}

/* Styles for answered questions */
.tracker-item.answered {
    background-color: #28a745; /* Green for answered */
    color: white;
}

/* Styles for unanswered questions */
.tracker-item.unanswered {
    background-color: #6c757d; /* Grey for unanswered */
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .tracker-item {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
    .tracker-container {
        gap: 5px;
    }
}   
 /* Add rounded bounding box around each question */
 .rounded-border {
        border: 1px solid #ddd;
        border-radius: 10px;
        background-color: #f8f9fa;
        padding: 15px;
    }

    /* Equal height for questions container and sidebar */
    .h-100 {
        height: 100%;
    }

    .flex-column {
        display: flex;
        flex-direction: column;
    }

    /* Scrollbar for Answer Tracker Widget */
    .overflow-y-auto {
        overflow-y: auto;
        max-height: 400px; /* Adjust height as needed */
    }

    /* Tracker Item Styles */
    .tracker-item {
        width: 30px;
        height: 30px;
        line-height: 30px;
        font-size: 14px;
        color: white;
        text-align: center;
    }

    .tracker-item.answered {
        background-color: #28a745;
    }

    .tracker-item.unanswered {
        background-color: rgb(180, 179, 179);
    }
    </style>