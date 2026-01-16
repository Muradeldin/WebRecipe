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

/* ===== Contact Form Handler ===== */

// Validate contact form fields
function validateContactForm(name, email, phone, message) {
    if (!name || !email || !phone || !message) {
        return { valid: false, error: "❌ אנא מלא את כל השדות!" };
    }
    // Basic email format check
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        return { valid: false, error: "❌ כתובת דוא\"ל לא תקינה!" };
    }
    return { valid: true };
}

// Show success message using innerHTML
function showSuccessMessage(outputBox, name) {
    outputBox.innerHTML = `
        <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;">
            <strong>✅ הצלחה!</strong><br>
            תודה ${name}! ההודעה נשלחת למייל הצוות.<br>
            <small>נחזור אליך בקרוב</small>
        </div>
    `;
    outputBox.style.display = "block";
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        outputBox.style.display = "none";
    }, 5000);
}

// Initialize contact form on page load
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("contactForm");
    if (!form) return;
    
    form.addEventListener("submit", function(event) {
        const name = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const message = document.getElementById("message").value.trim();
        
        // Validate form fields (ALERT output)
        const validation = validateContactForm(name, email, phone, message);
        if (!validation.valid) {
            window.alert(validation.error);
            event.preventDefault();
            return;
        }
        
        // Ask for email confirmation (PROMPT output)
        const confirmEmail = window.prompt(
            "אנא אשר את כתובת הדוא\"ל שלך:",
            email
        );
        
        if (confirmEmail === null) {
            window.alert("ביטול שליחה");
            event.preventDefault();
            return;
        }
        else {
            document.getElementById("email").value = confirmEmail;
            window.alert("כתובת הדוא\"ל אושרה!");
        }
        
        // Show success message (INNERHTML output)
        const outputBox = document.getElementById("message-output");
        if (outputBox) {
            showSuccessMessage(outputBox, name);
        }
        
        // Form will submit via mailto: action (no preventDefault needed)
    });
});


