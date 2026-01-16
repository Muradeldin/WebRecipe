<?php
// recipe_view.php
declare(strict_types=1);

require __DIR__ . "/php_db/db.php";

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($id <= 0) { http_response_code(404); exit("Recipe not found"); }

// 1) Recipe
$stmt = $pdo->prepare("
  SELECT r.id, r.title, r.serving, r.difficulty_id, d.name as difficulty_name, r.prep_minutes, r.image_src, r.video_src
  FROM recipes r
  LEFT JOIN difficulties d ON r.difficulty_id = d.id
  WHERE r.id = ?
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

// Difficulty: Get from difficulties table
$diffText = trim((string)($recipe["difficulty_name"] ?? ""));

// Servings: keep as text
$servingsText = $recipe["serving"] !== null ? (string)$recipe["serving"] : "";
$servingsNumeric = is_numeric($servingsText) ? (float)$servingsText : null;
$servingsInputDefault = $servingsNumeric !== null ? $servingsNumeric : 1;

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
      <li><a href="recipe-calculator.php">מחשבון מתכונים</a></li>
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
      <span>• רמת קושי: <?= h($diffText) ?></span>
    <?php endif; ?>

    <?php if ($servingsText !== ""): ?>
      <span>• מנות: <?= h($servingsText) ?></span>
    <?php endif; ?>
  </div>

  <div
    class="servings-control"
    id="servingsControl"
    data-base-servings="<?= h($servingsText) ?>">
    <label for="servingsInput">
      <a href="recipe-calculator.php" style="color: #2F7366; text-decoration: none; font-weight: bold;">
         חשב כמויות בעמוד מחשבון מתכונים
      </a>
    </label>
    <p class="servings-hint">
      <?= $servingsText !== "" ? "מבוסס על {$servingsText} מנות" : "לא צוין מספר מנות, ברירת מחדל 1" ?>
    </p>
  </div>

  <?php if ($videoEnabled): ?>
    <div class="recipe-actions recipe-actions-center">
      <a href="<?= h($videoSrc) ?>" target="_blank" rel="noopener noreferrer" class="cta-button">
        צפה בוידאו
      </a>
    </div>
  <?php endif; ?>

  <h2 class="recipe-section-title">מצרכים</h2>
  <ul class="recipe-list recipe-ingredients">
    <?php if (count($ingredientsOut) === 0): ?>
      <li>לא נמצאו מצרכים.</li>
    <?php else: ?>
      <?php foreach ($ings as $i): ?>
        <?php
          $name = trim((string)($i["name"] ?? ""));
          if ($name === "") continue;

          $amount = trim((string)($i["amount"] ?? ""));
          $measurement = trim((string)($i["measurement"] ?? ""));
        ?>
        <li
          class="ingredient-item"
          data-base-amount="<?= h($amount) ?>"
          data-measurement="<?= h($measurement) ?>"
          data-name="<?= h($name) ?>">
          <?php if ($amount !== ""): ?>
            <span class="ingredient-amount"><?= h($amount) ?></span>
          <?php endif; ?>
          <?php if ($measurement !== ""): ?>
            <span class="ingredient-measurement"><?= h($measurement) ?></span>
          <?php endif; ?>
          <span class="ingredient-name"><?= h($name) ?></span>
        </li>
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
            action="/php_db/delete_recipe.php"
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
    <div class="footer-content">
        <p>2026 The Flavor Forge &copy;</p>
        <p>פותח על ידי: <strong>נטלי, אדהם, מוראד</strong></p>
    </div>
</footer>

<script src="js/main.js"></script>

<?php // video toggle removed: link only (video handled on external tab) ?>

</body>
</html>
