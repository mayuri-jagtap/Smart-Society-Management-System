<!-- 🚨 Emergency Button -->
<button id="emergencyButton"
        style="position:fixed; bottom:90px; right:30px; z-index:9999;
               background:red; color:white; border:none; border-radius:50%;
               width:60px; height:60px; font-size:26px; cursor:pointer;
               box-shadow:0 3px 8px rgba(0,0,0,0.3);">
    🚨
</button>

<!-- 🔊 Siren Sound -->
<audio id="sirenSound" src="siren.mp3" preload="auto"></audio>

<script>
let sirenPlaying = false;
let lastAlertId = localStorage.getItem("lastAlertId") || null; // store last alert seen
const siren = document.getElementById("sirenSound");

// 🚨 When resident clicks button
document.getElementById("emergencyButton").addEventListener("click", function() {
    fetch("emergency_alert.php")
      .then(res => res.text())
      .then(response => {
          if (response.trim() === "alert_sent") {
              alert("🚨 Emergency alert sent!");
          }
      })
      .catch(err => console.error("Error sending alert:", err));
});

// 🔁 Check every 3 seconds for new alerts
setInterval(() => {
    fetch("check_emergency.php")
      .then(res => res.json())
      .then(data => {
          if (data.status === "alert" && data.id !== lastAlertId && !sirenPlaying) {
              // New alert detected
              sirenPlaying = true;
              lastAlertId = data.id;
              localStorage.setItem("lastAlertId", data.id);

              document.body.style.backgroundColor = "red";
              siren.play();

              setTimeout(() => {
                  siren.pause();
                  siren.currentTime = 0;
                  document.body.style.backgroundColor = "";
                  sirenPlaying = false;
              }, 10000); // stop after 10 seconds
          }
      })
      .catch(err => console.error("Emergency check failed:", err));
}, 3000);
</script>
