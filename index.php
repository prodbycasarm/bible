<?php
    require 'db.php';
    // Bible versions are stored
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

    $booksEnglish = [
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

    $booksFrench = [ 
        1 => "Genèse", 2 => "Exode", 3 => "Lévitique", 4 => "Nombres", 5 => "Deutéronome", 
        6 => "Josué", 7 => "Juges", 8 => "Ruth", 9 => "1 Samuel", 10 => "2 Samuel", 
        11 => "1 Rois", 12 => "2 Rois", 13 => "1 Chroniques", 14 => "2 Chroniques", 
        15 => "Esdras", 16 => "Néhémie", 17 => "Esther", 18 => "Job", 19 => "Psaumes", 
        20 => "Proverbes", 21 => "Ecclésiaste", 22 => "Cantique des cantiques", 
        23 => "Ésaïe", 24 => "Jérémie", 25 => "Lamentations", 26 => "Ézéchiel", 27 => "Daniel", 
        28 => "Osée", 29 => "Joël", 30 => "Amos", 31 => "Abdias", 32 => "Jonas", 33 => "Michée", 
        34 => "Nahum", 35 => "Habacuc", 36 => "Sophonie", 37 => "Aggée", 38 => "Zacharie", 
        39 => "Malachie", 40 => "Matthieu", 41 => "Marc", 42 => "Luc", 43 => "Jean", 
        44 => "Actes", 45 => "Romains", 46 => "1 Corinthiens", 47 => "2 Corinthiens", 
        48 => "Galates", 49 => "Éphésiens", 50 => "Philippiens", 51 => "Colossiens", 
        52 => "1 Thessaloniciens", 53 => "2 Thessaloniciens", 54 => "1 Timothée", 
        55 => "2 Timothée", 56 => "Tite", 57 => "Philémon", 58 => "Hébreux", 
        59 => "Jacques", 60 => "1 Pierre", 61 => "2 Pierre", 62 => "1 Jean", 
        63 => "2 Jean", 64 => "3 Jean", 65 => "Jude", 66 => "Apocalypse" ];

    $books = ($selectedVersion === "kjv") ? $booksEnglish : $booksFrench;
    
    $book = filter_input(INPUT_GET, 'book', FILTER_VALIDATE_INT) ?? 1;
    $chapter = filter_input(INPUT_GET, 'chapter', FILTER_VALIDATE_INT) ?? 1;

    if (!isset($books[$book])) {
        $book = 43;
    }

    if ($book < 1) {
        $book = 43;
    }

    if ($chapter < 1) {
        $chapter = 1;
    }

    $chapterQuery = $pdo->prepare("
        SELECT DISTINCT chapter
        FROM $table
        WHERE book = ?
        ORDER BY chapter
    ");

    $chapterQuery->execute([$book]);

    $chapters = $chapterQuery->fetchAll(PDO::FETCH_COLUMN);


    if (!in_array($chapter, $chapters)) {
        $chapter = $chapters[0];
    }

    $q = trim($_GET['q'] ?? '');
    $stmt = $pdo->prepare("
    SELECT book, chapter, verse, text
    FROM $table
    WHERE text LIKE ?
    ORDER BY book, chapter, verse
    LIMIT 100
    ");

    $stmt->execute(["%{$q}%"]);

    $verses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#0f1115" />
  <title><?= htmlspecialchars($books[$book]) ?> - Bible</title>

    <link rel="icon" href="public/god_is_love.png" type="image/png" sizes="32x32">
    <link rel="icon" href="public/god_is_love.png" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="public/casgod_is_lovearm_big.png" type="image/png">

    <!-- Save theme before paint to avoid a flash -->
    <script>
      (function () {
        try {
          var saved = localStorage.getItem("bible-theme");
          if (saved === "light") {
            document.documentElement.setAttribute("data-theme", "light");
          }
        } catch (e) {}
      })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="manifest" href="manifest.json">

</head>
<body>
    <header class="topbar">

        <div class="topbar-row">
          <h3 class="brand"><span class="brand-cross">†</span>Bible</h3>
            <span class="spacer"></span>
            <div class="topbar-actions">
                <button id="installBtn" class="install-btn" type="button" hidden>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    <span class="install-label">Install App</span>
                </button>
                
                <button id="themeToggle" class="theme-toggle" type="button" aria-label="Toggle light and dark mode" title="Toggle theme">
                    <!-- Moon (shown in dark mode) -->
                    <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                    </svg>
                    <!-- Sun (shown in light mode) -->
                    <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="picker">

        <form method="get" class="controls" id="searchForm">
            <?php
            
                $q = trim($_GET['q'] ?? '');
                if (strlen($q) > 100) {
                    $q = substr($q, 0, 100);
                }
                if ($q !== '') {

                    $stmt = $pdo->prepare("
                        SELECT `book`, `chapter`, `verse`, `text`
                        FROM $table
                        WHERE `text` LIKE ?
                        ORDER BY `book`, `chapter`, `verse`
                        LIMIT 100
                    ");

                    $stmt->execute(["%{$q}%"]);

                } else {

                    $stmt = $pdo->prepare("
                        SELECT `book`, `chapter`, `verse`, `text`
                        FROM $table
                        WHERE `book` = ?
                        AND `chapter` = ?
                        ORDER BY `verse`
                    ");

                    $stmt->execute([$book, $chapter]);
                }

                $verses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
            ?>
            <div class="search">
                <svg class="search__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input type="search" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>" id="searchInput" placeholder="Search verses…" aria-label="Search verses" autocomplete="off" />
            </div>
            <select name="bibleversion" onchange="this.form.submit()">
            <?php
            foreach ($bibleVersions as $key => $version) {

                $selected = ($selectedVersion == $key) ? "selected" : "";

                echo "<option value='" . htmlspecialchars($key) . "' $selected>"
                    . htmlspecialchars($version['name'])
                    . "</option>";
            }
            ?>
            </select>
            <select name="book" onchange="this.form.submit()">
                <?php
                foreach($books as $id=>$name){
                    $selected = ($book == $id) ? "selected" : "";

                    echo "<option value='" . htmlspecialchars($id) . "' $selected>"
                    . htmlspecialchars($name)
                    . "</option>";
                }


                
                ?>
            </select>

            <select name="chapter" onchange="this.form.submit()">
                <?php
                foreach($chapters as $ch){
                    $selected = ($chapter == $ch) ? "selected" : "";
                    echo "<option value='" . htmlspecialchars($ch) . "' $selected>Chapter " . htmlspecialchars($ch) . "</option>";
                }
                ?>
            </select>

            

        </form>

        </div>

    </header>

  <main class="reader">

    <h1 id="heading" class="heading">
      <?= htmlspecialchars($books[$book]) ?> <?= $chapter ?>
    </h1>
    <div id="verses" class="verses">
        <?php
        $i = 0;
        foreach($verses as $v){
            $delay = min($i, 30);
            echo "<div class='verse' id='verse-{$v['verse']}' style='--i:{$delay}'>";
            echo "<span class='number'>{$v['verse']}</span>";
            echo htmlspecialchars($v['text']);
            echo "</div>";
            $i++;
        }
        ?>

        <hr>
    </div>
  </main>

  <nav class="pager" aria-label="Chapter navigation">

  <div class="pager-buttons">
    <button id="prevBtn" class="pager-btn" type="button">‹ Previous</button>
    <button id="nextBtn" class="pager-btn" type="button">Next ›</button>
    
  </div>

  <div class="footer">
    &copy; <span id="year"></span> Casarm. All Rights Reserved
  </div>

</nav>


  <script>
    const book = <?= $book ?>;
    const chapter = <?= $chapter ?>;

    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");

    // Disable "Previous" on the first chapter
    if (chapter <= 1) {
        prevBtn.disabled = true;
    }

    // Previous chapter
    prevBtn.addEventListener("click", function(){
        if(chapter > 1){
            window.location.href =
                "?book=" + book + "&chapter=" + (chapter - 1);
        }
    });

    // Next chapter
    nextBtn.addEventListener("click", function(){
        window.location.href =
            "?book=" + book + "&chapter=" + (chapter + 1);
    });

    // ---------- Theme toggle ----------
    const themeToggle = document.getElementById("themeToggle");
    const root = document.documentElement;

    themeToggle.addEventListener("click", function(){
        const isLight = root.getAttribute("data-theme") === "light";
        const next = isLight ? "dark" : "light";

        if (next === "light") {
            root.setAttribute("data-theme", "light");
        } else {
            root.removeAttribute("data-theme");
        }

        try { localStorage.setItem("bible-theme", next); } catch (e) {}

        // Keep the mobile browser chrome color in sync
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute("content", next === "light" ? "#f6f7f9" : "#0f1115");
    });





    const heading = document.getElementById("heading");

    function setSearching(searching) {
        heading.classList.toggle("hide", searching);
    }
    const input = document.getElementById("searchInput");
    const verses = document.getElementById("verses");

    let timer;
    let controller;

    input.addEventListener("input", () => {

        const q = input.value.trim();

        clearTimeout(timer);

        timer = setTimeout(async () => {

            if (controller) {
                controller.abort();
            }
            if (q === "") {
                return;
            }

            setSearching(true);

            controller = new AbortController();

            try {
                const version = document.querySelector("[name='bibleversion']").value;

                const response = await fetch(
                    "search.php?q=" + encodeURIComponent(q) 
                    + "&bibleversion=" + encodeURIComponent(version),
                    {
                        signal: controller.signal
                    }
                );

                verses.innerHTML = await response.text();

            } catch (e) {
                if (e.name !== "AbortError") {
                    console.error(e);
                }
            }

        }, 400);

    });
    // This prevents the form from submitting when pressing Enter in the search input
    document.getElementById("searchForm").addEventListener("submit", (e) => {
        e.preventDefault();
    });

    // This is what we use to target the verse achor when the page loads with a verse query parameter (e.g., ?verse=5)
    const params = new URLSearchParams(window.location.search);
    const verseNumber = params.get("verse");

    if (verseNumber) {
            const verse = document.getElementById("verse-" + verseNumber);

            if (verse) {
                setTimeout(() => {
                    verse.scrollIntoView({
                        behavior: "smooth",
                        block: "center"
                    });

                    verse.classList.add("highlight-verse");
                }, 300);
            }
    }

    document.getElementById("year").textContent = new Date().getFullYear();


    // For updates

    if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("/sw.js")
    .then(registration => {
        // Check for updates every time the user opens the app
        registration.update();
    });
}


    // ---------- PWA Install ----------

    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("sw.js");
    }

    let deferredPrompt;

    const installBtn = document.getElementById("installBtn");

    // Hide if already installed/running as a PWA
    function updateInstallButton() {
        const installed =
            window.matchMedia("(display-mode: standalone)").matches ||
            window.navigator.standalone === true; // iOS

        installBtn.hidden = installed;
    }

    updateInstallButton();

    //Hide the install buttonfor IOS
    
    const isIOS =
        /iPad|iPhone|iPod/.test(navigator.userAgent) ||
        (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);

    if (isIOS) {
        installBtn.hidden = true;
    }
    window.addEventListener("beforeinstallprompt", (e) => {
        e.preventDefault();

        if (isIOS) return;

        deferredPrompt = e;
        installBtn.hidden = false;
    });


    window.addEventListener("appinstalled", () => {
        console.log("App installed");

        deferredPrompt = null;
        installBtn.hidden = true;
    });

    installBtn.addEventListener("click", async () => {
        if (!deferredPrompt) return;

        deferredPrompt.prompt();

        const { outcome } = await deferredPrompt.userChoice;

        console.log(outcome);

        deferredPrompt = null;

        if (outcome === "accepted") {
            installBtn.hidden = true;
        }
    });


    


  </script>
</body>
</html>
