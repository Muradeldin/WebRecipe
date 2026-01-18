

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
        // 1. STOP the form from submitting normally (Fixes "Not Secure" warning)
        event.preventDefault();

        const name = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const message = document.getElementById("message").value.trim();
        
        const confirmEmail = window.prompt("אנא אשר את כתובת הדוא\"ל שלך:", email);
        if (confirmEmail === null) {
            return;
        }

        const subject = `New Message from ${name} (The Flavor Forge)`;
        const emailBody = `שם: ${name}\r\nאימייל: ${confirmEmail}\r\nטלפון: ${phone}\r\n\r\nהודעה:\r\n${message}`;
        
        // 4. Trigger the email client
        window.location.href = `mailto:moradeldin2@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(emailBody)}`;        
        // 5. Show success message on the site
        const outputBox = document.getElementById("message-output");
        if (outputBox) {
            showSuccessMessage(outputBox, name);
        }
        
        // Optional: Clear the form after sending
        form.reset();
    });
});


