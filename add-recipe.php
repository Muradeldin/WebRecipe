<?php
// add-recipe.php
declare(strict_types=1);

require __DIR__ . "/php_db/db.php";

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Fetch all difficulties from the database
$stmt = $pdo->query("SELECT id, name FROM difficulties ORDER BY id ASC");
$difficulties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>The Flavor Forge - הוסף מתכון</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/recipe_creation.css">
</head>

<body>
<header>
  <div class="logo-container">
    <img src="images/logo.png" alt="לוגו The Flavor Forge" class="site-logo">
  </div>
  <nav>
    <ul>
      <li><a href="index.html">דף הבית</a></li>
      <li><a href="recipes.php">כל המתכונים</a></li>
      <li><a href="add-recipe.php" class="active">הוסף מתכון</a></li>
      <li><a href="recipe-calculator.php">מחשבון מתכונים</a></li>
      <li><a href="team.html">הצוות</a></li>
    </ul>
  </nav>
</header>

<div class="hero-title">
  <div class="hero-title-content">
    <h1 class="page-title">הוסף מתכון</h1>
    <p class="page-subtitle">ממלאים פרטים, מוסיפים שורות למצרכים/שלבים, ושומרים.</p>
  </div>
</div>


<main class="recipes-section">
  <div class="interactive-section form-card" style="text-align:right;">
    <form method="post" action="/php_db/store-recipe.php" id="addRecipeForm" novalidate>
      <div class="form-group">
        <label for="title">שם המתכון</label>
        <input id="title" name="title" type="text" required placeholder="לדוגמה: עוגת שוקולד" />
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label for="serving">כמות מנות</label>
          <input id="serving" name="serving" type="number" min="1" required placeholder="2" />
        </div>

        <div class="form-group">
          <label for="difficulty">רמת קושי</label>
          <select id="difficulty" name="difficulty_id" required>
            <option value="">-- בחר רמת קושי --</option>
            <?php foreach ($difficulties as $d): ?>
              <option value="<?= (int)$d["id"] ?>"><?= h($d["name"]) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label for="prep_minutes">זמן הכנה בדקות</label>
          <input id="prep_minutes" name="prep_minutes" type="number" min="0" required placeholder="10" />
        </div>

        <div class="form-group">
          <label for="image_src">קישור לתמונה (אופציונלי)</label>
          <input id="image_src" name="image_src" type="text" placeholder="https://..." />
        </div>
      </div>

      <div class="form-group">
        <label for="video_src">קישור לוידאו (אופציונלי)</label>
        <input id="video_src" name="video_src" type="text" placeholder="https://..." />
      </div>

      <div class="section-title">מצרכים</div>
      <table id="ingredientsTable">
        <thead>
          <tr>
            <th>שם מצרך</th>
            <th>כמות</th>
            <th>יחידת מידה</th>
            <th class="row-actions">פעולות</th>
          </tr>
        </thead>
        <tbody>
          <tr class="ingredient-row">
            <td>
              <input type="text" name="ingredients[name][]" required placeholder="קמח">
            </td>
            <td>
              <input type="number" step="0.01" name="ingredients[amount][]" required placeholder="1">
            </td>
            <td>
              <input type="text" name="ingredients[measurement][]" required placeholder="כוס">
            </td>
            <td class="row-actions">
              <button type="button" class="mini-btn add js-add-row">+</button>
              <button type="button" class="mini-btn remove js-remove-row">−</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="section-title">שלבי הכנה</div>
      <table id="stepsTable">
        <thead>
          <tr>
            <th>תיאור שלב</th>
            <th class="row-actions">פעולות</th>
          </tr>
        </thead>
        <tbody>
          <tr class="step-row">
            <td><input type="text" name="steps[description][]" required placeholder="לדוגמה: מחממים תנור ל-180 מעלות" /></td>
            <td class="row-actions">
              <button type="button" class="mini-btn add js-add-row" data-target="steps">+</button>
              <button type="button" class="mini-btn remove js-remove-row" data-target="steps">−</button>
            </td>
          </tr>
        </tbody>
      </table>

      <button type="submit" class="cta-button" style="margin-top: 16px;">שמור מתכון</button>

      <?php if (isset($_GET["ok"]) && $_GET["ok"] === "1"): ?>
        <div class="success" style="display:block;">המתכון נשמר בהצלחה ✅</div>
      <?php endif; ?>

      <?php if (isset($_GET["err"])): ?>
        <div class="error" style="display:block;">שגיאה: <?php echo htmlspecialchars($_GET["err"]); ?></div>
      <?php endif; ?>
    </form>
  </div>
</main>

<footer>
    <div class="footer-content">
        <p>2026 The Flavor Forge &copy;</p>
        <p>פותח על ידי: <strong>מוראד, אדהם, נטלי</strong></p>
    </div>
</footer>

<script src="js/create_recipe.js"></script>
</body>
</html>
