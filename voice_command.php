<!-- 🎤 Common Voice Command Button for Residents -->
<button id="micButton" 
    title="Click and speak page name"
    style="position: fixed; bottom: 30px; right: 30px; z-index: 9999;
           background: #007bff; color: white; border: none; border-radius: 50%;
           width: 55px; height: 55px; font-size: 24px; cursor: pointer;
           box-shadow: 0 3px 8px rgba(0,0,0,0.3);">
    🎤
</button>

<script>
document.getElementById("micButton").addEventListener("click", function() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        alert("Speech Recognition not supported in this browser.");
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = 'en-IN';
    recognition.start();

    const micBtn = document.getElementById("micButton");
    micBtn.style.background = "red";

    recognition.onend = () => {
        micBtn.style.background = "#007bff";
    };

    recognition.onresult = function(event) {
        const speech = event.results[0][0].transcript.toLowerCase().trim();
        console.log("You said:", speech);

        // Resident voice commands
        if (speech.includes("maintenance")) window.location.href = "maintenance.php";
        else if (speech.includes("visitor")) window.location.href = "visitor.php";
        else if (speech.includes("facility")) window.location.href = "facility.php";
        else if (speech.includes("service")) window.location.href = "service.php";
        else if (speech.includes("payment")) window.location.href = "payment.php";
        else if (speech.includes("complaint")) window.location.href = "complaints.php";
        else if (speech.includes("logout")) window.location.href = "logout.php";
        else if (speech.includes("Maintenance Bill Payment")) window.location.href = "maintenance_payment.php";
        

        else alert("Sorry, I didn’t recognize that page name.");
    };
});
</script>
