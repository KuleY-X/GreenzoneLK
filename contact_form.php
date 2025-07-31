<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'greenzonelk';
$username = 'root'; // Change as needed
$password = '';     // Change as needed

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html?error=' . urlencode('Invalid request method'));
    exit();
}

// Validate and sanitize input
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Basic validation
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Name is required and must be at least 2 characters';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email address is required';
}

if (empty($subject)) {
    $errors[] = 'Subject is required';
}

if (empty($message) || strlen($message) < 10) {
    $errors[] = 'Message is required and must be at least 10 characters';
}

// If there are validation errors, redirect back with errors
if (!empty($errors)) {
    $errorString = implode(', ', $errors);
    header('Location: contact.html?error=' . urlencode($errorString));
    exit();
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Insert contact form submission
    $stmt = $pdo->prepare("
        INSERT INTO contact_submissions (name, email, phone, subject, message, submitted_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$name, $email, $phone, $subject, $message]);
    
    // Send email notification (optional)
    $to = 'info@greenzonelk.com';
    $email_subject = 'New Contact Form Submission - ' . $subject;
    $email_body = "
        New contact form submission from GreenzoneLk website:
        
        Name: $name
        Email: $email
        Phone: $phone
        Subject: $subject
        
        Message:
        $message
        
        Submitted at: " . date('Y-m-d H:i:s') . "
    ";
    
    $headers = "From: noreply@greenzonelk.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    
    // Uncomment the line below to enable email notifications
    // mail($to, $email_subject, $email_body, $headers);
    
    // Redirect with success message
    header('Location: contact.html?success=' . urlencode('Thank you for your message! We will get back to you within 24 hours.'));
    exit();
    
} catch (PDOException $e) {
    // Log error (in production, don't show actual error to user)
    error_log("Contact form error: " . $e->getMessage());
    header('Location: contact.html?error=' . urlencode('Sorry, there was a problem submitting your message. Please try again later.'));
    exit();
}
?>