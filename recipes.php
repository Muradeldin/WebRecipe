<?php
// recipes.php
declare(strict_types=1);

require __DIR__ . "/php_db/db.php";

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Fetch recipes (latest first)
$stmt = $pdo->query("
  SELECT id, title, prep_minutes, difficulty, image_src, video_src
  FROM recipes
  ORDER BY id DESC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Difficulty: numeric (1-5) -> Hebrew text (optional, but nicer)
$diffMap = [
  1 => "קל",
  2 => "קל-בינוני",
  3 => "בינוני",
  4 => "בינוני-קשה",
  5 => "קשה",
];
?>
<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>The Flavor Forge - כל המתכונים</title>
  <link href="css/style.css" rel="stylesheet" />
</head>
<body>

<header>
  <div class="logo-container">
    <img alt="לוגו The Flavor Forge" class="site-logo" src="images/logo.png" />
  </div>
  <nav>
    <ul>
      <li><a href="index.html">דף הבית</a></li>
      <li><a class="active" href="recipes.php">כל המתכונים</a></li>
      <li><a href="add-recipe.php">הוסף מתכון</a></li>
      <li><a href="team.html">הצוות</a></li>
    </ul>
  </nav>
</header>

<h1 class="page-title">כל המתכונים</h1>
<p class="page-subtitle">
  כאן תמצאו אוסף מתכונים ביתיים. לכל מתכון יש תמונה, ובחלק מהם יש גם וידיאו להמחשה.
</p>

<main class="recipes-section">
  <section aria-label="רשימת מתכונים" class="recipes-grid">
    <?php if (count($rows) === 0): ?>
      <p>אין מתכונים להצגה.</p>
    <?php else: ?>
      <?php foreach ($rows as $r): ?>
        <?php
          $id = (int)$r["id"];
          $title = trim((string)($r["title"] ?? ""));
          if ($title === "") $title = "ללא שם";

          $prep = $r["prep_minutes"] !== null ? (int)$r["prep_minutes"] : null;
          $diffNum = $r["difficulty"] !== null ? (int)$r["difficulty"] : null;
          $diffText = $diffNum !== null ? ($diffMap[$diffNum] ?? (string)$diffNum) : "";

          $hasVideo = trim((string)($r["video_src"] ?? "")) !== "";

          $img = trim((string)($r["image_src"] ?? ""));
          if ($img === "") $img = "images/logo.png";

          // Build meta line
          $metaParts = [];
          if ($prep !== null) $metaParts[] = "זמן הכנה: {$prep} דק׳";
          if ($diffText !== "") $metaParts[] = "רמת קושי: {$diffText}";
          $meta = implode(" • ", $metaParts);
        ?>

        <!-- Card -->
        <article class="recipe-card">
          <a href="<?= h("recipe_view.php?id=" . $id) ?>" class="recipe-card-link">
            <img class="recipe-card-img" src="<?= h($img) ?>" alt="<?= h($title) ?>" />
            <div class="recipe-card-body">
              <h2 class="recipe-card-title"><?= h($title) ?></h2>

              <?php if ($meta !== ""): ?>
                <p class="recipe-card-meta"><?= h($meta) ?></p>
              <?php endif; ?>

              <?php if ($hasVideo): ?>
                <span class="recipe-card-badge">כולל וידיאו</span>
              <?php endif; ?>
            </div>
          </a>
        </article>

      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<footer>
  <p>© 2025 The Flavor Forge</p>
  <p>כל הזכויות שמורות</p>
</footer>

<script src="js/main.js"></script>

</body>
</html>
