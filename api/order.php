<?php
session_start();
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

// Validate required fields
$required = ['name', 'email', 'address', 'city', 'country', 'zip'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

// Validate email
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Get cart
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    http_response_code(400);
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

// Calculate total
$total = 0;
$items_text = "";
foreach ($cart as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $items_text .= "- {$item['name']} x{$item['quantity']} = \${$subtotal}\n";
}

// Generate order number
$order_number = 'PP-' . strtoupper(substr(md5(time() . rand()), 0, 8));

// Save order to file (simple storage - replace with database in production)
$order = [
    'order_number' => $order_number,
    'customer' => [
        'name' => htmlspecialchars($input['name']),
        'email' => htmlspecialchars($input['email']),
        'phone' => htmlspecialchars($input['phone'] ?? ''),
        'address' => htmlspecialchars($input['address']),
        'city' => htmlspecialchars($input['city']),
        'country' => htmlspecialchars($input['country']),
        'zip' => htmlspecialchars($input['zip'])
    ],
    'items' => array_values($cart),
    'total' => $total,
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
];

// Save to orders file
$orders_file = __DIR__ . '/../data/orders.json';
$orders_dir = dirname($orders_file);
if (!is_dir($orders_dir)) {
    mkdir($orders_dir, 0755, true);
}

$orders = [];
if (file_exists($orders_file)) {
    $orders = json_decode(file_get_contents($orders_file), true) ?? [];
}
$orders[] = $order;
file_put_contents($orders_file, json_encode($orders, JSON_PRETTY_PRINT));

// Send email notification (configure your email settings)
$to = $input['email'];
$subject = "Order Confirmation - $order_number";
$message = "Thank you for your order!\n\n";
$message .= "Order Number: $order_number\n\n";
$message .= "Items:\n$items_text\n";
$message .= "Total: \$$total\n\n";
$message .= "Shipping to:\n";
$message .= "{$input['name']}\n";
$message .= "{$input['address']}\n";
$message .= "{$input['city']}, {$input['zip']}\n";
$message .= "{$input['country']}\n\n";
$message .= "We'll send you tracking information once your order ships.\n\n";
$message .= "- The PurePeptide Team";

$headers = "From: orders@aura-peptide.com\r\n";
$headers .= "Reply-To: support@aura-peptide.com\r\n";

// Attempt to send email (may fail on some hosts without proper config)
@mail($to, $subject, $message, $headers);

// Clear cart after successful order
$_SESSION['cart'] = [];

echo json_encode([
    'success' => true,
    'order_number' => $order_number,
    'total' => $total,
    'message' => 'Order placed successfully!'
]);
?>
