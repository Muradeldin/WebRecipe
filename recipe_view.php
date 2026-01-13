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
    <label for="servingsInput">חשב כמויות לפי מספר מנות</label>
    <div class="servings-control-actions">
      <input
        type="number"
        id="servingsInput"
        min="0.25"
        step="0.25"
        value="<?= h((string)$servingsInputDefault) ?>"
      />
      <button type="button" class="cta-button" id="servingsApply">חשב</button>
      <button type="button" class="cta-button secondary" id="servingsReset">איפוס</button>
    </div>
    <p class="servings-hint">
      <?= $servingsText !== "" ? "מבוסס על {$servingsText} מנות" : "לא צוין מספר מנות, ברירת מחדל 1" ?>
    </p>
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
  <p>© 2025 The Flavor Forge</p>
  <p>כל הזכויות שמורות</p>
</footer>

<script src="js/main.js"></script>

<script>
(function servingsCalculator() {
  const control = document.getElementById('servingsControl');
  if (!control) return;

  const input = document.getElementById('servingsInput');
  const applyBtn = document.getElementById('servingsApply');
  const resetBtn = document.getElementById('servingsReset');
  const hint = control.querySelector('.servings-hint');

  const baseServingsRaw = (control.dataset.baseServings || '').trim();
  const baseServingsParsed = parseAmount(baseServingsRaw);
  const fallbackBaseServings = baseServingsParsed || parseAmount(input?.value || '') || 1;

  const ingredients = Array.from(document.querySelectorAll('.recipe-ingredients .ingredient-item')).map((li) => ({
    element: li,
    baseAmountRaw: (li.dataset.baseAmount || '').trim(),
    baseAmount: parseAmount(li.dataset.baseAmount || ''),
    amountSpan: li.querySelector('.ingredient-amount'),
  }));

  function parseAmount(raw) {
    if (!raw) return null;
    const clean = raw.replace(',', '.').trim();

    // Handle mixed numbers like "1 1/2"
    const mixed = clean.match(/^(-?\d+)\s+(\d+)\/(\d+)$/);
    if (mixed) {
      const whole = Number(mixed[1]);
      const num = Number(mixed[2]);
      const den = Number(mixed[3]) || 1;
      return whole + num / den;
    }

    // Handle simple fractions "3/4"
    const frac = clean.match(/^(-?\d+)\/(\d+)$/);
    if (frac) {
      const num = Number(frac[1]);
      const den = Number(frac[2]) || 1;
      return num / den;
    }

    const num = Number(clean);
    return Number.isFinite(num) ? num : null;
  }

  function formatAmount(val) {
    if (!Number.isFinite(val)) return '';
    if (Math.abs(val - Math.round(val)) < 0.001) return String(Math.round(val));
    return parseFloat(val.toFixed(2)).toString();
  }

  function updateHint(target) {
    if (!hint) return;
    const baseText = baseServingsRaw !== '' ? baseServingsRaw : formatAmount(fallbackBaseServings);
    const currentText = target ? formatAmount(target) : baseText;
    hint.textContent = `מבוסס על ${baseText} מנות • עכשיו: ${currentText} מנות`;
  }

  function applyScale() {
    const target = parseAmount(input?.value || '');
    if (!target || target <= 0) return;

    const factor = target / fallbackBaseServings;

    ingredients.forEach((item) => {
      if (item.baseAmount === null || !item.amountSpan) return;
      const scaled = item.baseAmount * factor;
      item.amountSpan.textContent = formatAmount(scaled);
    });

    updateHint(target);
  }

  function resetScale() {
    const base = baseServingsParsed || fallbackBaseServings;
    if (input) input.value = formatAmount(base);

    ingredients.forEach((item) => {
      if (item.amountSpan) item.amountSpan.textContent = item.baseAmountRaw;
    });

    hint.textContent = baseServingsRaw !== ''
      ? `מבוסס על ${baseServingsRaw} מנות`
      : `מבוסס על ${formatAmount(base)} מנות`;
  }

  applyBtn?.addEventListener('click', (ev) => {
    ev.preventDefault();
    applyScale();
  });

  input?.addEventListener('change', applyScale);

  resetBtn?.addEventListener('click', (ev) => {
    ev.preventDefault();
    resetScale();
  });

  resetScale();
})();
</script>

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
