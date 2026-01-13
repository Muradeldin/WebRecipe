function showMessage() {
    document.getElementById("message").innerText =
        "Message sent successfully! We will get back to you soon.";
}

/* במקום index.html — חוזר לדף הצוות */
function goHome() {
    window.location.href = "team.html";
}

/* מהדף team יש אפשרות לעבור ל-index */
function goIndex() {
    window.location.href = "index.html";
}

/* גלילה ליצירת קשר בדף הצוות */
function scrollToContact() {
    const el = document.getElementById("contact");
    if (el) el.scrollIntoView({ behavior: "smooth" });
}

/* --- Recipes Page: toggle video blocks --- */

function toggleRecipeVideo(videoId, btn) {
  const wrap = document.getElementById(videoId);
  if (!wrap) return;

  const isOpen = wrap.classList.toggle("show"); // חייב "show" לפי ה-CSS שלך

  // עדכון טקסט בכפתור
  if (btn) btn.textContent = isOpen ? "הסתר וידאו" : "הצג וידאו";

  // גלילה לוידאו כשהוא נפתח (אופציונלי אבל נחמד)
  if (isOpen) {
    wrap.scrollIntoView({ behavior: "smooth", block: "start" });
  }
}

/* Contact Form: send mail (mailto) + show confirmation  */
function sendMessage(event) {
  // חשוב: לא עושים preventDefault כדי לא לחסום את mailto
  const nameEl = document.getElementById("name");
  const outputBox = document.getElementById("message-output");

  if (outputBox && nameEl) {
    outputBox.textContent = `תודה ${nameEl.value} !   ההודעה נשלחת למייל הצוות נחזור אלייך בקרוב ל-`;
    outputBox.style.display = "block";
    outputBox.style.backgroundColor = "#d4edda";
    outputBox.style.color = "#155724";
  }
}


