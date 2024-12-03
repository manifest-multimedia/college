
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



/* Tracker Styles */
.question-tracker {
    max-width: 300px;
    text-align: center;
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