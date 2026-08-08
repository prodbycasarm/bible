<?php

require 'db.php'; // your PDO connection

// Bible versions 
$bibleVersions = [
    "kjv" => [
        "name" => "King James Version",
        "table" => "bible_kjv"
    ],
    "segond1910" => [
        "name" => "Louis Segond 1910",
        "table" => "bible_verses_segond_1910"
    ],
    "martin" => [
        "name" => "Martin",
        "table" => "bible_verses_martin"
    ]
];

$selectedVersion = $_GET['bibleversion'] ?? "kjv";

// fallback if invalid
if (!isset($bibleVersions[$selectedVersion])) {
    $selectedVersion = "segond1910";
}

$table = $bibleVersions[$selectedVersion]['table'];


$q = trim($_GET['q'] ?? '');

if ($q === '') {
    exit;
}
if (mb_strlen($q) > 100) {
    $q = mb_substr($q, 0, 100);
}
$stmt = $pdo->prepare("
    SELECT book, chapter, verse, text
    FROM $table
    WHERE text LIKE ?
    ORDER BY book, chapter, verse
    LIMIT 100
");



// Book names
$books = [
    1=>"Genesis",2=>"Exodus",3=>"Leviticus",4=>"Numbers",5=>"Deuteronomy",
    6=>"Joshua",7=>"Judges",8=>"Ruth",9=>"1 Samuel",10=>"2 Samuel",
    11=>"1 Kings",12=>"2 Kings",13=>"1 Chronicles",14=>"2 Chronicles",
    15=>"Ezra",16=>"Nehemiah",17=>"Esther",18=>"Job",19=>"Psalms",
    20=>"Proverbs",21=>"Ecclesiastes",22=>"Song of Solomon",23=>"Isaiah",
    24=>"Jeremiah",25=>"Lamentations",26=>"Ezekiel",27=>"Daniel",
    28=>"Hosea",29=>"Joel",30=>"Amos",31=>"Obadiah",32=>"Jonah",
    33=>"Micah",34=>"Nahum",35=>"Habakkuk",36=>"Zephaniah",37=>"Haggai",
    38=>"Zechariah",39=>"Malachi",40=>"Matthew",41=>"Mark",42=>"Luke",
    43=>"John",44=>"Acts",45=>"Romans",46=>"1 Corinthians",
    47=>"2 Corinthians",48=>"Galatians",49=>"Ephesians",50=>"Philippians",
    51=>"Colossians",52=>"1 Thessalonians",53=>"2 Thessalonians",
    54=>"1 Timothy",55=>"2 Timothy",56=>"Titus",57=>"Philemon",
    58=>"Hebrews",59=>"James",60=>"1 Peter",61=>"2 Peter",
    62=>"1 John",63=>"2 John",64=>"3 John",65=>"Jude",66=>"Revelation"
];


$stmt->execute(["%{$q}%"]);

$verses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($verses as $v) {

    $text = htmlspecialchars($v['text'], ENT_QUOTES, 'UTF-8');

    if ($q !== '') {
        $words = preg_split('/\s+/', $q);

        foreach ($words as $word) {
            if ($word === '') continue;

            $text = preg_replace(
                '/' . preg_quote($word, '/') . '/i',
                '<mark>$0</mark>',
                $text
            );
        }
    }

    echo "<a class='verse' href='?bibleversion="
    . htmlspecialchars($selectedVersion, ENT_QUOTES, 'UTF-8')
    . "&book="
    . (int)$v['book']
    . "&chapter="
    . (int)$v['chapter']
    . "&verse="
    . (int)$v['verse']
    . "'>";

    echo "<span class='number'>";
    echo $books[$v['book']] . " " . $v['chapter'] . ":" . $v['verse'];
    echo "</span> ";

    echo $text;

    echo "</a>";
}