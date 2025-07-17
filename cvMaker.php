<?php
// Add strict session configuration
session_set_cookie_params([
    'lifetime' => 86400, // 1 day
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require_once 'db.php';


// Check if user is logged in as intern
if (!isset($_SESSION['user_id']) ) {
    header("Location: Login.php");
    exit();
}

$intern_id = $_SESSION['user_id']; // Use user_id as intern_id
// Debugging output (remove in production)
error_log("Session ID: " . session_id());
error_log("Intern ID: " . ($_SESSION['intern_id'] ?? 'Not set'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nextntern CV Maker</title>
    <style>
        /* Keep only essential form styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
        }
        
        body {
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        header {
            background-color: #1c1a85;
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .section-title {
            font-size: 1.3rem;
            color: #1c1a85;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #f5f5f5;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="url"],
        textarea,
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            background-color: #1c1a85;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 20px;
            width: 100%;
        }
        
        .btn:hover {
            background-color: #15136b;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
    <header>
        <div class="logo-container">
            <div class="header-title">CV Maker</div>
        </div>
    </header>

    <div class="container">
        <form id="cvForm">
            <!-- Personal Information -->
            <div class="section-title">Personal Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" placeholder="Sehrish" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" placeholder="Rahman" required>
                </div>
            </div>

            <div class="form-group">
                <label for="profession">Profession/Title</label>
                <input type="text" id="profession" placeholder="Software Developer" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="sehrish.ama@example.com" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" placeholder="+880 01*******" required>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" placeholder="Road, City, Country" required>
            </div>

            <div class="form-group">
                <label for="summary">Professional Summary</label>
                <textarea id="summary" placeholder="Briefly describe your professional background and skills" required></textarea>
            </div>

            <!-- Education -->
            <div class="section-title">Education</div>
            <div id="educationContainer">
                <div class="education-item">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="degree1">Degree</label>
                            <input type="text" id="degree1" placeholder="Bachelor of Science in Computer Science" required>
                        </div>
                        <div class="form-group">
                            <label for="institution1">Institution</label>
                            <input type="text" id="institution1" placeholder="University of Example" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="educationStart1">Start Date</label>
                            <input type="text" id="educationStart1" placeholder="2018" required>
                        </div>
                        <div class="form-group">
                            <label for="educationEnd1">End Date</label>
                            <input type="text" id="educationEnd1" placeholder="2022" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Experience -->
            <div class="section-title">Work Experience</div>
            <div id="experienceContainer">
                <div class="experience-item">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jobTitle1">Job Title</label>
                            <input type="text" id="jobTitle1" placeholder="Software Developer Intern" required>
                        </div>
                        <div class="form-group">
                            <label for="company1">Company</label>
                            <input type="text" id="company1" placeholder="Tech Company Inc." required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="experienceStart1">Start Date</label>
                            <input type="text" id="experienceStart1" placeholder="June 2021" required>
                        </div>
                        <div class="form-group">
                            <label for="experienceEnd1">End Date</label>
                            <input type="text" id="experienceEnd1" placeholder="August 2021" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="experienceDescription1">Description</label>
                        <textarea id="experienceDescription1" placeholder="Your responsibilities and achievements" required></textarea>
                    </div>
                </div>
            </div>

            <!-- Skills -->
            <div class="section-title">Skills</div>
            <div class="form-group">
                <label for="skills">List your skills (comma separated)</label>
                <input type="text" id="skills" placeholder="JavaScript, Python, React, Teamwork, Communication" required>
            </div>

            <button type="button" id="savePdfBtn" class="btn">Save CV to Server</button>
        </form>
    </div>

    <script>
     document.addEventListener('DOMContentLoaded', function() {
    const saveBtn = document.getElementById('savePdfBtn');
    
    saveBtn.addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'pt', 'a4');
        const margin = 40;
        let yPos = 40;
        const lineHeight = 24;
        
        // Helper function to add text with styling
        function addText(text, style = {}) {
            const fontSize = style.fontSize || 12;
            const fontWeight = style.fontWeight || 'normal';
            const fontStyle = style.fontStyle || 'normal';
            const textAlign = style.textAlign || 'left';
            
            pdf.setFontSize(fontSize);
            pdf.setFont('helvetica', fontStyle, fontWeight);
            pdf.setTextColor(0, 0, 0);
            
            const pageWidth = pdf.internal.pageSize.getWidth();
            let xPos = margin;
            
            if (textAlign === 'center') {
                const textWidth = pdf.getStringUnitWidth(text) * fontSize / pdf.internal.scaleFactor;
                xPos = (pageWidth - textWidth) / 2;
            } else if (textAlign === 'right') {
                const textWidth = pdf.getStringUnitWidth(text) * fontSize / pdf.internal.scaleFactor;
                xPos = pageWidth - margin - textWidth;
            }
            
            pdf.text(text, xPos, yPos);
            yPos += lineHeight * (fontSize / 12);
        }
        
        // Helper function to add section headings
        function addSectionHeading(text) {
            addText(text, {fontSize: 16, fontWeight: 'bold'});
            yPos += 10;
        }
        
        // Add personal information
        addText(`${document.getElementById('firstName').value} ${document.getElementById('lastName').value}`, 
                {fontSize: 24, fontWeight: 'bold', textAlign: 'center'});
        yPos += 10;
        
        addText(document.getElementById('profession').value, 
                {fontSize: 18, textAlign: 'center', fontStyle: 'italic'});
        yPos += 20;
        
        addText(`${document.getElementById('email').value} | ${document.getElementById('phone').value} | ${document.getElementById('address').value}`, 
                {textAlign: 'center'});
        yPos += 30;
        
        // Add summary
        addSectionHeading("Professional Summary");
        const summaryText = pdf.splitTextToSize(document.getElementById('summary').value, pdf.internal.pageSize.getWidth() - margin * 2);
        summaryText.forEach(line => {
            addText(line);
        });
        yPos += 20;
        
        // Add education
        addSectionHeading("Education");
        const educationItems = document.querySelectorAll('.education-item');
        educationItems.forEach(item => {
            addText(item.querySelector('[id^="degree"]').value, {fontWeight: 'bold'});
            addText(`${item.querySelector('[id^="institution"]').value} | ${item.querySelector('[id^="educationStart"]').value} - ${item.querySelector('[id^="educationEnd"]').value}`);
            yPos += 10;
        });
        yPos += 10;
        
        // Add experience
        addSectionHeading("Work Experience");
        const experienceItems = document.querySelectorAll('.experience-item');
        experienceItems.forEach(item => {
            addText(item.querySelector('[id^="jobTitle"]').value, {fontWeight: 'bold'});
            addText(`${item.querySelector('[id^="company"]').value} | ${item.querySelector('[id^="experienceStart"]').value} - ${item.querySelector('[id^="experienceEnd"]').value}`);
            
            const descriptionText = pdf.splitTextToSize(
                item.querySelector('[id^="experienceDescription"]').value, 
                pdf.internal.pageSize.getWidth() - margin * 2
            );
            
            descriptionText.forEach(line => {
                addText(line);
            });
            yPos += 10;
        });
        yPos += 10;
        
        // Add skills
        addSectionHeading("Skills");
        addText(document.getElementById('skills').value);
        
        // Save PDF to a blob
        const pdfBlob = pdf.output('blob');
        const fileName = `cv_${Date.now()}.pdf`;
        
        // Create form data for upload
        const formData = new FormData();
        formData.append('cv_file', pdfBlob, fileName);
        
        // Send to server
        fetch('save_cv.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(res => res.text())
        .then(res => {
            alert(res);
        })
        .catch(err => {
            console.error(err);
            alert('Failed to save CV');
        });
    });
});
    </script>
</body>
</html>