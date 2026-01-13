<?php
// recipe_view.php
declare(strict_types=1);

require __DIR__ . "/php_db/db.php";

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($id <= 0) { http_response_code(404); exit("Recipe not found"); }

// 1) Recipe
$stmt = $pdo->prepare("
  SELECT id, title, serving, difficulty, prep_minutes, image_src, video_src
  FROM recipes
  WHERE id = ?
");
$stmt->execute([$id]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$recipe) { http_response_code(404); exit("Recipe not found"); }

// 2) Ingredients
$stmt = $pdo->prepare("
  SELECT name, amount, measurement
  FROM recipe_ingredients
  WHERE recipe_id = ?
  ORDER BY ingredient_order ASC
");
$stmt->execute([$id]);
$ings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3) Steps
$stmt = $pdo->prepare("
  SELECT description
  FROM recipe_steps
  WHERE recipe_id = ?
  ORDER BY step_number ASC
");
$stmt->execute([$id]);
$steps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Difficulty: DB is numeric (1-5). UI expects Hebrew text.
$diffNum = $recipe["difficulty"] !== null ? (int)$recipe["difficulty"] : null;
$diffMap = [
  1 => "קל",
  2 => "קל-בינוני",
  3 => "בינוני",
  4 => "בינוני-קשה",
  5 => "קשה",
];
$diffText = $diffNum !== null ? ($diffMap[$diffNum] ?? (string)$diffNum) : "";

// Servings: keep as text
$servingsText = $recipe["serving"] !== null ? (string)$recipe["serving"] : "";

// Image fallback: logo
$imageSrc = trim((string)($recipe["image_src"] ?? ""));
if ($imageSrc === "") $imageSrc = "images/logo.png";

// Video
$videoSrc = trim((string)($recipe["video_src"] ?? ""));
$videoEnabled = ($videoSrc !== "");

// Build ingredients array as strings: "2 כוס קמח" or "קמח"
$ingredientsOut = [];
foreach ($ings as $i) {
  $name = trim((string)($i["name"] ?? ""));
  if ($name === "") continue;

  $amount = trim((string)($i["amount"] ?? ""));
  $measurement = trim((string)($i["measurement"] ?? ""));

  $prefix = trim($amount . " " . $measurement);
  $line = trim(($prefix !== "" ? $prefix . " " : "") . $name);

  $ingredientsOut[] = $line;
}

// Steps array
$instructionsOut = [];
foreach ($steps as $s) {
  $desc = trim((string)($s["description"] ?? ""));
  if ($desc === "") continue;
  $instructionsOut[] = $desc;
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h("The Flavor Forge - " . (string)$recipe["title"]) ?></title>
  <link href="css/style.css" rel="stylesheet" />
</head>
<body>

<header>
  <div class="logo-container">
    <img src="images/logo.png" alt="לוגו The Flavor Forge" class="site-logo" />
  </div>
  <nav>
    <ul>
      <li><a href="index.html">דף הבית</a></li>
      <li><a href="recipes.php">כל המתכונים</a></li>
      <li><a href="add-recipe.php">הוסף מתכון</a></li>
      <li><a href="team.html">הצוות</a></li>
    </ul>
  </nav>
</header>

<h1 class="page-title"><?= h((string)$recipe["title"]) ?></h1>
<p class="page-subtitle"></p>

<main class="interactive-section recipe-page">

  <img
    src="<?= h($imageSrc) ?>"
    alt="<?= h((string)$recipe["title"]) ?>"
    class="recipe-hero-img"
  />

  <div class="recipe-meta recipe-meta-block">
    <?php if ($recipe["prep_minutes"] !== null): ?>
      <span> זמן הכנה: <?= (int)$recipe["prep_minutes"] ?> דקות</span>
    <?php endif; ?>

    <?php if ($diffText !== ""): ?>
      <span> רמת קושי: <?= h($diffText) ?></span>
    <?php endif; ?>

    <?php if ($servingsText !== ""): ?>
      <span>🍽 מנות: <?= h($servingsText) ?></span>
    <?php endif; ?>
  </div>

  <?php if ($videoEnabled): ?>
    <div class="recipe-actions recipe-actions-center">
      <button class="cta-button" type="button" id="toggleVideoBtn">הצג וידאו</button>
    </div>

    <div class="video-wrap" id="recipeVideo" style="display:none;">
      <video controls>
        <source src="<?= h($videoSrc) ?>" type="video/mp4" />
        הדפדפן שלך לא תומך בוידאו.
      </video>
    </div>
  <?php endif; ?>

  <h2 class="recipe-section-title">מצרכים</h2>
  <ul class="recipe-list recipe-ingredients">
    <?php if (count($ingredientsOut) === 0): ?>
      <li>לא נמצאו מצרכים.</li>
    <?php else: ?>
      <?php foreach ($ingredientsOut as $line): ?>
        <li><?= h($line) ?></li>
      <?php endforeach; ?>
    <?php endif; ?>
  </ul>

  <h2 class="recipe-section-title">אופן הכנה</h2>
  <ol class="recipe-list recipe-steps">
    <?php if (count($instructionsOut) === 0): ?>
      <li>לא נמצאו שלבים.</li>
    <?php else: ?>
      <?php foreach ($instructionsOut as $step): ?>
        <li><?= h($step) ?></li>
      <?php endforeach; ?>
    <?php endif; ?>
  </ol>

    <div class="recipe-actions recipe-actions-center">

      <a class="cta-button"
         href="recipes.php"
         style="width:320px; display:inline-block; text-align:center;">
        חזרה לכל המתכונים
      </a>

      <form method="post"
            action="delete_recipe.php"
            onsubmit="return confirm('האם אתה בטוח שברצונך למחוק מתכון זה?');"
            style="display:inline-block;">

        <input type="hidden" name="id" value="<?= (int)$recipe["id"] ?>" />

        <button type="submit"
                class="cta-button delete-button"
                style="width:320px;">
          מחק מתכון
        </button>

      </form>

    </div>


</main>

<footer>
  <p>© 2025 The Flavor Forge</p>
  <p>כל הזכויות שמורות</p>
</footer>

<script src="js/main.js"></script>

<?php if ($videoEnabled): ?>
<script>
  // simple toggle (no recipe-render.js needed)
  const btn = document.getElementById('toggleVideoBtn');
  const wrap = document.getElementById('recipeVideo');
  let shown = false;

  btn.addEventListener('click', () => {
    shown = !shown;
    wrap.style.display = shown ? 'block' : 'none';
    btn.textContent = shown ? 'הסתר וידאו' : 'הצג וידאו';
  });
</script>
<?php endif; ?>

</body>
</html>
