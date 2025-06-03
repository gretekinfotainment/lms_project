// webcam.js
function initWebcam() {
    const video = document.getElementById('video');
    navigator.mediaDevices.getUserMedia({ video: true })
        .then((stream) => {
            video.srcObject = stream;
        })
        .catch((err) => {
            console.error("Error accessing webcam: ", err);
        });
}

function capturePhoto() {
    const canvas = document.getElementById('canvas');
    const video = document.getElementById('video');
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, 320, 240);
    const dataUrl = canvas.toDataURL('image/png');
    document.getElementById('photoData').value = dataUrl;
    document.getElementById('photoForm').submit();
}
