<div>
    <div class="d-flex flex-column align-items-end">
        <button class="btn btn-danger" wire:click="stopProctoring">Stop Proctoring</button>
        <button class="btn btn-success" 
            wire:click="saveRecording">Save Recording</button>

        <div class="d-flex flex-column align-items-center">
            <div class="p-1 border border-3 border-secondary rounded-circle d-flex justify-content-center align-items-center" style="width:200px; height:200px;">
                <video id="proctoringVideo" autoplay muted playsinline></video>
            </div>
        </div>
    </div>

    <script>
        let mediaRecorder;
        let recordedChunks = [];

        document.addEventListener('livewire:init', () => {
            Livewire.on('startProctoring', () => {
                navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                    .then((stream) => {
                        const videoElement = document.getElementById('proctoringVideo');
                        videoElement.srcObject = stream;
                        videoElement.play();

                        // Initialize Media Recorder
                        mediaRecorder = new MediaRecorder(stream);

                        mediaRecorder.ondataavailable = (event) => {
                            if (event.data.size > 0) {
                                recordedChunks.push(event.data);
                            }
                        };

                        mediaRecorder.onstop = () => {
                            console.log("Recording stopped.");
                        };

                        mediaRecorder.start();
                    })
                    .catch((error) => {
                        console.error("Error accessing the camera: ", error);
                        alert("Please enable camera access for proctoring.");
                    });
            });

            Livewire.on('stopProctoring', () => {
                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    mediaRecorder.stop();
                    const tracks = document.getElementById('proctoringVideo').srcObject.getTracks();
                    tracks.forEach(track => track.stop());
                }
            });

            Livewire.on('saveRecording', () => {
                if (recordedChunks.length > 0) {
                    const blob = new Blob(recordedChunks, { type: 'video/webm' });
                    const file = new File([blob], 'recording.webm', { type: 'video/webm' });

                    // Use Livewire's file upload system
                    Livewire.emit('fileUpload', file);
                } else {
                    alert('No recording available to save.');
                }
            });

            Livewire.on('recordingSaved', (path) => {
                alert(`Recording saved successfully! Path: ${path}`);
            });

            Livewire.on('recordingFailed', (message) => {
                alert(`Recording failed: ${message}`);
            });
        });
    </script>

    <style>
        #proctoringVideo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</div>
