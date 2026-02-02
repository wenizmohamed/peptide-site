<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate
if (empty($input['name']) || empty($input['email']) || empty($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Name, email, and message are required']);
    exit;
}

if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Save to file
$contact = [
    'name' => htmlspecialchars($input['name']),
    'email' => htmlspecialchars($input['email']),
    'subject' => htmlspecialchars($input['subject'] ?? 'General Inquiry'),
    'message' => htmlspecialchars($input['message']),
    'created_at' => date('Y-m-d H:i:s')
];

$contacts_file = __DIR__ . '/../data/contacts.json';
$contacts_dir = dirname($contacts_file);
if (!is_dir($contacts_dir)) {
    mkdir($contacts_dir, 0755, true);
}

$contacts = [];
if (file_exists($contacts_file)) {
    $contacts = json_decode(file_get_contents($contacts_file), true) ?? [];
}
$contacts[] = $contact;
file_put_contents($contacts_file, json_encode($contacts, JSON_PRETTY_PRINT));

// Send email
$to = "support@aura-peptide.com";
$subject = "Contact Form: " . ($input['subject'] ?? 'General Inquiry');
$message = "Name: {$input['name']}\n";
$message .= "Email: {$input['email']}\n\n";
$message .= "Message:\n{$input['message']}";

$headers = "From: {$input['email']}\r\n";
$headers .= "Reply-To: {$input['email']}\r\n";

@mail($to, $subject, $message, $headers);

echo json_encode([
    'success' => true,
    'message' => 'Thank you for your message! We\'ll get back to you soon.'
]);
?>
