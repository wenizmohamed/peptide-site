<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // Get cart contents
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        echo json_encode([
            'items' => array_values($_SESSION['cart']),
            'total' => $total,
            'count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
        ]);
        break;

    case 'POST':
        // Add to cart
        if (isset($input['product_id'])) {
            $id = $input['product_id'];
            $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$id] = [
                    'product_id' => $id,
                    'name' => $input['name'] ?? 'Product',
                    'price' => $input['price'] ?? 0,
                    'image' => $input['image'] ?? '',
                    'quantity' => $quantity
                ];
            }
            echo json_encode(['success' => true, 'message' => 'Added to cart']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID required']);
        }
        break;

    case 'PUT':
        // Update quantity
        if (isset($input['product_id']) && isset($input['quantity'])) {
            $id = $input['product_id'];
            if (isset($_SESSION['cart'][$id])) {
                if ($input['quantity'] <= 0) {
                    unset($_SESSION['cart'][$id]);
                } else {
                    $_SESSION['cart'][$id]['quantity'] = (int)$input['quantity'];
                }
                echo json_encode(['success' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Item not in cart']);
            }
        }
        break;

    case 'DELETE':
        // Remove from cart or clear cart
        if (isset($input['product_id'])) {
            $id = $input['product_id'];
            unset($_SESSION['cart'][$id]);
            echo json_encode(['success' => true, 'message' => 'Removed from cart']);
        } else {
            $_SESSION['cart'] = [];
            echo json_encode(['success' => true, 'message' => 'Cart cleared']);
        }
        break;
}
?>
