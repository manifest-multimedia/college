<style>
/* Basic scrollbar styles */

::-webkit-scrollbar {
    width: 5px; /* Adjust width of scrollbar */
    height: 12px; /* Adjust height for horizontal scrolling */
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background-color: #888;
    border-radius: 10px; /* Make the thumb rounded */
    transition: background-color 0.3s ease; /* Add a smooth transition */
}

::-webkit-scrollbar-thumb:hover {
    background-color: #555;
}

::-webkit-scrollbar-corner {
    background-color: transparent;
}

/* Scrollbar buttons (arrows) */
::-webkit-scrollbar-button {
    background-color: transparent; /* Make the buttons invisible by default */
    display: none; /* Hide the buttons initially */
}

/* Show the scroll up and down arrows only */
.scrollbar-container {
  position: relative;
  width: 100%;
  height: 100%;
  overflow-y: auto;
}

.scrollbar-thumb {
  position: absolute;
  top: 0;
  left: 0;
  width: 10px;
  height: 100%;
  background-color: #ccc;
  border-radius: 10px;
}

.scrollbar-button {
  position: absolute;
  width: 20px;
  height: 20px;
  background-color: #ccc;
  border-radius: 50%;
  cursor: pointer;
}

.scrollbar-button.up {
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  background-image: url('https://img.icons8.com/ios/50/000000/chevron-up.png');
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
}

.scrollbar-button.down {
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  background-image: url('https://img.icons8.com/ios/50/000000/chevron-down.png');
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
}
</style>