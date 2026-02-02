<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$products = [
    [
        'id' => 1,
        'name' => 'Peptide Pen Pro',
        'description' => 'Precision applicator with adjustable dosage control',
        'price' => 189,
        'image' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=400&h=400&fit=crop',
        'tag' => 'Bestseller',
        'stock' => 50
    ],
    [
        'id' => 2,
        'name' => 'Charging Dock',
        'description' => 'Elegant wireless charging station',
        'price' => 49,
        'image' => 'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?w=400&h=400&fit=crop',
        'tag' => null,
        'stock' => 100
    ],
    [
        'id' => 3,
        'name' => 'Cleaning Kit',
        'description' => 'Complete maintenance set for longevity',
        'price' => 29,
        'image' => 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop',
        'tag' => null,
        'stock' => 200
    ],
    [
        'id' => 4,
        'name' => 'Peptide Serum Refill',
        'description' => 'Premium peptide formula - 30ml',
        'price' => 79,
        'image' => 'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?w=400&h=400&fit=crop',
        'tag' => 'New',
        'stock' => 75
    ],
    [
        'id' => 5,
        'name' => 'Complete Starter Kit',
        'description' => 'Everything you need to begin your journey',
        'price' => 249,
        'image' => 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=400&h=400&fit=crop',
        'tag' => 'Best Value',
        'stock' => 30
    ]
];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $product = array_filter($products, fn($p) => $p['id'] === $id);
        if ($product) {
            echo json_encode(array_values($product)[0]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
        }
    } else {
        echo json_encode($products);
    }
}
?>
