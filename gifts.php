<?php

require "Database.php";

$config = require("config.php");
$db = new Database($config["database"]);

// Iegūst dāvanu nosaukumus un to pieejamo skaitu
$gifts = $db->query("SELECT id, name, count_available FROM gifts")->fetchAll();

// Iegūst bērnu vēstules
$query = "SELECT l.letter_text 
          FROM letters l
          INNER JOIN children c ON l.sender_id = c.id";
$children_with_letters = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Izveido dāvanu vēlmju skaitu masīvu (kā atslēgu izmanto dāvanu ID)
$gift_wishlist_count = [];

// Saskaņo vēstules ar dāvanu vēlmēm
foreach ($children_with_letters as $child) {
    $letter_text = $child['letter_text'];

    foreach ($gifts as $gift) {
        // Pārbauda, vai vēstulē ir minēta dāvana
        if (stripos($letter_text, $gift['name']) !== false) {
            // Ja bērns vēlas šo dāvanu, palielina vēlmes skaitu
            if (!isset($gift_wishlist_count[$gift['id']])) {
                $gift_wishlist_count[$gift['id']] = 0;
            }
            $gift_wishlist_count[$gift['id']]++;
        }
    }
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Gifts List</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fef5e7; }
        ol { color: #34495e; }
        h1 { color: #2c3e50; }
        .gift-card { margin: 10px 0; padding: 10px; background: #ecf0f1; border-radius: 5px; }
        .status-ok { color: green; font-weight: bold; }
        .status-warning { color: orange; font-weight: bold; }
        .status-error { color: red; font-weight: bold; }
        h1 { text-align: center; }
    </style>
</head>
<body>

<h1>Salaveca Davanas</h1>
<ol>";

foreach ($gifts as $gift) {
    $available = $gift['count_available'];
    $wishlist_count = isset($gift_wishlist_count[$gift['id']]) ? $gift_wishlist_count[$gift['id']] : 0;

    echo "<li class='gift-card'>
            <strong>{$gift['name']}</strong><br>
            Daudzums noliktavā: {$available}<br>
            Vēlmēs skaits: {$wishlist_count}<br>";

    // Statusa pārbaude - vai dāvanas pietiek
    if ($wishlist_count > $available) {
        echo "<span class='status-error'>Trūkst! Ir vairāk vēlmju nekā dāvanu.</span>";
    } elseif ($wishlist_count < $available) {
        echo "<span class='status-warning'>Ir par daudz! Dāvanas ir vairāk nekā vēlmju.</span>";
    } else {
        echo "<span class='status-ok'>Pietiek! Dāvanu skaits atbilst vēlmēm.</span>";
    }

    echo "</li>";
}

echo "</ol>
</body>
</html>";
?>
