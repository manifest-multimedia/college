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