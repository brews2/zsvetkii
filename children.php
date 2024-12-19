<?php  

require "Database.php";

$config = require("config.php");
$db = new Database($config["database"]);

// 1. Ielādē bērnu vēstules
$query = "SELECT c.id, c.firstname, c.middlename, c.surname, c.age, l.letter_text 
          FROM children c
          INNER JOIN letters l ON c.id = l.sender_id";
$children_with_letters = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// 2. Ielādē dāvanu nosaukumus
$gifts_query = "SELECT name FROM gifts";
$gifts = $db->query($gifts_query)->fetchAll(PDO::FETCH_COLUMN); // Atgriež dāvanu nosaukumu masīvu

// 3. Iegūst bērnu vidējās atzīmes
$query_grades = "SELECT student_id, AVG(grade) AS avg_grade 
                 FROM grades 
                 GROUP BY student_id";
$student_grades = $db->query($query_grades)->fetchAll(PDO::FETCH_ASSOC);

// Izveido masīvu, kas uzglabā bērnu vidējās atzīmes
$child_grades = [];
foreach ($student_grades as $grade) {
    $child_grades[$grade['student_id']] = $grade['avg_grade'];
}

// Pārbauda, vai dāvanas ir ielādētas
if (empty($gifts)) {
    die("Kļūda: Tabulā 'gifts' nav dāvanu ierakstu.");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Bērnu Vēstules</title>
    <link href='https://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Lato', sans-serif;
            background: #CC231E; /* Christmas red */
            color: #FFF;
            overflow-y: auto;
        }

        /* Snowflake container */
        .snowflakes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        h1 {
            text-align: center;
            margin: 20px;
            font-size: 36px;
            z-index: 10;
        }

        .content {
            position: relative;
            z-index: 10;
            padding: 20px;
        }

        .card {
            background: rgba(255, 255, 255, 0.9); /* White with opacity */
            color: #333;
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #CC231E;
        }

        p {
            font-size: 16px;
        }

        .highlight {
            font-weight: bold;
            color: #CC231E; /* Red for highlighted gifts */
        }

        .low-grade {
            color: red;
        }

        .high-grade {
            color: green;
        }

        ul {
            padding-left: 20px;
            list-style-type: square;
        }

        /* Snowflake styles */
        .snowflake {
            position: absolute;
            top: -10px;
            width: 10px;
            height: 10px;
            background: #FFF;
            border-radius: 50%;
            opacity: 0.7;
            animation: fall linear infinite;
        }

        @keyframes fall {
            to {
                transform: translateY(100vh);
            }
        }
    </style>
</head>
<body>
    <div class='snowflakes'></div> <!-- Snowflakes container -->

    <h1>Bērnu Vēstules: <br>
    <img src='pretty.avif' width='200' height='300'> <br>
    <img src='vecis.png' width='400' height='200'>
    </h1>
    
    <div class='content'>";

foreach ($children_with_letters as $child) {
    $child_id = $child['id'];
    $letter_text = $child['letter_text'];
    $matched_gifts = [];

    // Pārbauda vidējo atzīmi un piešķir krāsu
    $avg_grade = isset($child_grades[$child_id]) ? $child_grades[$child_id] : 0;
    $grade_class = ($avg_grade < 5) ? 'low-grade' : 'high-grade'; // Sarkans vai zaļš atkarībā no atzīmes

    // Meklē un izceļ dāvanu nosaukumus vēstulē
    foreach ($gifts as $gift) {
        if (stripos($letter_text, $gift) !== false) {
            $letter_text = preg_replace("/\b" . preg_quote($gift, "/") . "\b/i", "<span class='highlight'>$gift</span>", $letter_text);
            $matched_gifts[] = $gift;
        }
    }

    // Izvada bērna kartiņu
    echo "<div class='card'>
            <h2>{$child['firstname']} {$child['middlename']} {$child['surname']} ({$child['age']} gadi)</h2>
            <p class='{$grade_class}'><strong>Vidējā atzīme:</strong> {$avg_grade}</p>
            <p><strong>Vēstule:</strong> $letter_text</p>";

    // Ja vidējā atzīme ir zem 5, pievieno attēlu "angry.png"
    if ($avg_grade < 5) {
        echo "<p><img src='angry.jpg' alt='Angry Face' style='width:150px; height:150px;'></p>";
    }

    // Izvada atrasto dāvanu sarakstu
    if (!empty($matched_gifts)) {
        echo "<p><strong>Vēlmju saraksts:</strong></p><ul>";
        foreach ($matched_gifts as $gift) {
            echo "<li>$gift</li>";
        }
        echo "</ul>";
    } else {
        echo "<p><strong>Vēlmju saraksts:</strong> Nav atrastas dāvanas.</p>";
    }

    echo "</div>";
}

echo "</div>

    <script>
        // Snowflake container
        const snowflakesContainer = document.querySelector('.snowflakes');

        function createSnowflake() {
            const snowflake = document.createElement('div');
            snowflake.classList.add('snowflake');
            snowflake.style.left = Math.random() * 100 + 'vw';
            snowflake.style.animationDuration = Math.random() * 3 + 2 + 's';
            snowflake.style.width = snowflake.style.height = Math.random() * 10 + 5 + 'px';
            snowflake.style.opacity = Math.random();
            snowflakesContainer.appendChild(snowflake);

            setTimeout(() => {
                snowflake.remove();
            }, 5000);
        }

        setInterval(createSnowflake, 50);
    </script>
</body>
</html>";
?>
