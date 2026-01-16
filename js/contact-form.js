// js/contact-form.js
// Demonstrates all JS output methods: alert, document.write, innerHTML, prompt

document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("contactForm");
    
    if (form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            
            // Collect form data
            const name = document.getElementById("name").value.trim();
            const email = document.getElementById("email").value.trim();
            const phone = document.getElementById("phone").value.trim();
            const message = document.getElementById("message").value.trim();
            
            // ====== 1. WINDOW.ALERT() - Alert Box Output ======
            if (!name || !email || !phone || !message) {
                window.alert("❌ אנא מלא את כל השדות!");
                return;
            }
            
            // ====== 2. WINDOW.PROMPT() - Get confirmation ======
            const confirmSend = window.prompt(
                "אנא אשר את כתובת הדוא\"ל שלך:\n" + email,
                email
            );
            
            if (confirmSend === null) {
                window.alert("ביטול שליחה");
                return;
            }
            
            // Send data via AJAX to PHP backend
            const formData = new FormData();
            formData.append("name", name);
            formData.append("email", email);
            formData.append("phone", phone);
            formData.append("message", message);
            
            // Show loading message using innerHTML
            const outputDiv = document.getElementById("message-output");
            outputDiv.innerHTML = "<p style='color: #2F7366;'>⏳ שולח הודעה...</p>";
            outputDiv.style.display = "block";
            
            fetch("php_db/send-contact.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // ====== 3. INNERHTML - Write to HTML element ======
                if (data.success) {
                    outputDiv.innerHTML = `
                        <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;">
                            <strong>✅ הצלחה!</strong><br>
                            ${data.message}<br>
                            <small>נשלחנו אלייך דוא"ל של אישור</small>
                        </div>
                    `;
                    outputDiv.style.backgroundColor = "#d4edda";
                    outputDiv.style.borderTop = "4px solid #28a745";
                    
                    // ====== 4. WINDOW.ALERT() - Success message ======
                    window.alert("✅ ההודעה נשלחה בהצלחה!\nתודה על יצירת הקשר.");
                    
                    // Clear form
                    form.reset();
                    
                    // Auto-hide after 5 seconds
                    setTimeout(() => {
                        outputDiv.style.display = "none";
                    }, 5000);
                } else {
                    const errorMsg = data.errors ? data.errors.join("\n") : data.message;
                    
                    // ====== INNERHTML - Error display ======
                    outputDiv.innerHTML = `
                        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;">
                            <strong>❌ שגיאה:</strong><br>
                            ${errorMsg}
                        </div>
                    `;
                    outputDiv.style.backgroundColor = "#f8d7da";
                    outputDiv.style.borderTop = "4px solid #dc3545";
                    
                    // ====== WINDOW.ALERT() - Error notification ======
                    window.alert("❌ שגיאה בשליחת ההודעה:\n" + errorMsg);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                
                // ====== INNERHTML - Network error ======
                outputDiv.innerHTML = `
                    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;">
                        <strong>❌ שגיאת חיבור:</strong><br>
                        אנא בדוק את ההעברה ונסה שוב
                    </div>
                `;
                outputDiv.style.backgroundColor = "#f8d7da";
                outputDiv.style.borderTop = "4px solid #dc3545";
                
                // ====== WINDOW.ALERT() - Network error ======
                window.alert("❌ שגיאה בשליחת ההודעה. אנא נסה שוב מאוחר יותר.");
            });
        });
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList && e.target.classList.contains("show-info")) {
        e.preventDefault();
        
        const infoWindow = window.open("", "info", "width=400,height=300");
        
        // ====== DOCUMENT.WRITE() - Write to new window ======
        infoWindow.document.write(`
            <!DOCTYPE html>
            <html dir="rtl" lang="he">
            <head>
                <meta charset="UTF-8">
                <title>מידע על The Flavor Forge</title>
                <style>
                    body { font-family: Arial; direction: rtl; padding: 20px; background: #f5f5f5; }
                    h1 { color: #1B3C59; }
                    p { line-height: 1.6; }
                    button { padding: 10px 20px; background: #F29F05; border: none; cursor: pointer; }
                </style>
            </head>
            <body>
                <h1>The Flavor Forge</h1>
                <p>מוראד, אדהם ונטלי מציגים:</p>
                <p>המקום האולטימטיבי לניהול היצירתיות שלכם במטבח.</p>
                <p><strong>עלינו:</strong></p>
                <ul>
                    <li>ניהול מתכונים מתקדם</li>
                    <li>מחשבון עלויות מוקדש</li>
                    <li>שיתוף קהילתי</li>
                </ul>
                <button onclick="window.close()">סגור</button>
            </body>
            </html>
        `);
        infoWindow.document.close();
    }
});
