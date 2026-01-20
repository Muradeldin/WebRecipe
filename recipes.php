<?php
// recipes.php
declare(strict_types=1);

require __DIR__ . "/php_db/db.php";

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Fetch recipes (latest first)
$stmt = $pdo->query("
  SELECT r.id, r.title, r.prep_minutes, r.difficulty_id, d.name as difficulty_name, r.image_src, r.video_src
  FROM recipes r
  LEFT JOIN difficulties d ON r.difficulty_id = d.id
  ORDER BY r.id DESC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
      <li><a href="recipe-calculator.php">מחשבון מתכונים</a></li>
      <li><a href="team.html">הצוות</a></li>
    </ul>
  </nav>
</header>

<div class="hero">
  <div class="hero-content">
    <h1>כל המתכונים</h1>
    <p>
      כאן תמצאו אוסף מתכונים ביתיים. לכל מתכון יש תמונה, ובחלק מהם יש גם וידיאו להמחשה.
    </p>
  </div>
</div>

<main class="recipes-section">
  <section aria-label="רשימת מתכונים" class="recipes-grid" id="recipesGrid">

    <?php if (!$rows): ?>
      <p>אין מתכונים להצגה.</p>
    <?php else: ?>

      <?php foreach ($rows as $r): ?>
        <?php
          $id = (int)$r["id"];
          $title = trim((string)($r["title"] ?? "")) ?: "מתכון";

          $prep = $r["prep_minutes"] !== null ? (int)$r["prep_minutes"] : null;
          $diffText = trim((string)($r["difficulty_name"] ?? ""));

          $metaParts = [];
          if ($prep !== null) $metaParts[] = "זמן הכנה: {$prep} דק׳";
          if ($diffText !== "") $metaParts[] = "רמת קושי: {$diffText}";
          $meta = implode(" • ", $metaParts);

          // Construct image path from recipe title
          $sanitizedTitle = preg_replace('/[^א-תa-z0-9]/i', '_', $title);
          $sanitizedTitle = preg_replace('/_+/', '_', $sanitizedTitle);
          $sanitizedTitle = trim($sanitizedTitle, '_');
          
          $img = "images/recipe_img/" . $sanitizedTitle;
          // Check for common image extensions
          if (file_exists("images/recipe_img/{$sanitizedTitle}.jpg")) {
            $img .= ".jpg";
          } elseif (file_exists("images/recipe_img/{$sanitizedTitle}.png")) {
            $img .= ".png";
          } elseif (file_exists("images/recipe_img/{$sanitizedTitle}.gif")) {
            $img .= ".gif";
          } elseif (file_exists("images/recipe_img/{$sanitizedTitle}.webp")) {
            $img .= ".webp";
          } elseif (file_exists("images/recipe_img/{$sanitizedTitle}.jpeg")) {
            $img .= ".jpeg";
          } else {
            $img = "images/logo.png";
          }

          $hasVideo = trim((string)($r["video_src"] ?? "")) !== "";

          $page = "recipe_view.php?id=" . $id;
        ?>

        <!-- EXACT structure like recipes-list.js -->
        <article class="recipe-card">

          <?php if ($img !== ""): ?>
            <img src="<?= h($img) ?>" alt="<?= h($title) ?>" />
          <?php endif; ?>

          <div class="recipe-content">
            <h3><?= h($title) ?></h3>

            <div class="recipe-meta"><?= h($meta) ?></div>

            <div class="recipe-actions">
              <a class="cta-button" href="<?= h($page) ?>">למתכון המלא</a>
            </div>

            <?php if ($hasVideo): ?>
              <div style="margin-top:10px;font-size:12px;opacity:0.85;">
                כולל וידאו
              </div>
            <?php endif; ?>
          </div>

        </article>

      <?php endforeach; ?>

    <?php endif; ?>

  </section>
</main>

<footer>
    <div class="footer-content">
        <p>2026 The Flavor Forge &copy;</p>
        <p>פותח על ידי: <strong>מוראד, אדהם, נטלי</strong></p>
    </div>
</footer>

<script src="js/main.js"></script>

</body>
</html>
