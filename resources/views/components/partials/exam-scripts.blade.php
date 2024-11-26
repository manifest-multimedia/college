<script>
    // Countdown Timer
    let countdownElement = document.getElementById('countdown');
    
    let duration=@json($exam->duration); // in Minutes
    
    //conver duration to seconds;
    duration=duration*60;
    
    let remainingTime = duration; 
    
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
      const strikeCount = document.getElementById('strikeCount');
        if (strikeCount) {
          strikeCount.textContent = `You have ${maxAttempts - attemptCount} attempts left`;
        }
    
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
    
      //   show count on id with stirkeCount
       
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
    