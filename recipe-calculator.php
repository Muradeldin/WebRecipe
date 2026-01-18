<?php
// recipe-calculator.php - Recipe Ingredient Calculator
// User picks a recipe and scales ingredients by servings

declare(strict_types=1);

require __DIR__ . "/php_db/db.php";

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Fetch all recipes
$stmt = $pdo->query("SELECT id, title FROM recipes ORDER BY title ASC");
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedRecipe = null;
$ingredients = [];
$recipeId = isset($_GET["recipe_id"]) ? (int)$_GET["recipe_id"] : 0;

if ($recipeId > 0) {
    // Fetch recipe details
    $stmt = $pdo->prepare("
        SELECT id, title, serving
        FROM recipes
        WHERE id = ?
    ");
    $stmt->execute([$recipeId]);
    $selectedRecipe = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($selectedRecipe) {
        // Fetch ingredients
        $stmt = $pdo->prepare("
            SELECT name, amount, measurement
            FROM recipe_ingredients
            WHERE recipe_id = ?
            ORDER BY ingredient_order ASC
        ");
        $stmt->execute([$recipeId]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Flavor Forge - מחשבון מתכונים</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .calculator-section {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-top: 5px solid #F29F05;
        }

        .recipe-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            align-items: end;
        }

        .selector-group {
            display: flex;
            flex-direction: column;
        }

        .selector-group label {
            font-weight: bold;
            color: #1B3C59;
            margin-bottom: 8px;
        }

        .selector-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .select-btn {
            background-color: #F29F05;
            color: #1B3C59;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .select-btn:hover {
            background-color: #d98e04;
        }

        .recipe-details {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
        }

        .recipe-title {
            color: #1B3C59;
            font-size: 1.8em;
            margin-bottom: 20px;
        }

        .servings-control {
            background-color: #e8f4f8;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-right: 4px solid #2F7366;
        }

        .servings-control label {
            font-weight: bold;
            color: #1B3C59;
            display: block;
            margin-bottom: 15px;
        }

        .servings-control-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .servings-control-actions input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            width: 150px;
        }

        .servings-control-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }

        .apply-btn {
            background-color: #2F7366;
            color: white;
        }

        .apply-btn:hover {
            background-color: #1f5149;
        }

        .reset-btn {
            background-color: #ddd;
            color: #333;
        }

        .reset-btn:hover {
            background-color: #bbb;
        }

        .servings-hint {
            color: #2F7366;
            font-size: 0.9em;
            margin: 0;
        }

        .recipe-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .recipe-list li {
            padding: 12px;
            margin: 8px 0;
            background-color: #f9f9f9;
            border-right: 4px solid #F29F05;
            border-radius: 5px;
        }

        .ingredient-amount {
            font-weight: bold;
            color: #1B3C59;
            margin-right: 5px;
        }

        .ingredient-measurement {
            color: #2F7366;
            margin-right: 5px;
        }

        .ingredient-name {
            color: #333;
        }

        .recipe-section-title {
            color: #1B3C59;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .no-recipe {
            text-align: center;
            color: #666;
            padding: 40px 20px;
        }

        @media (max-width: 700px) {
            .recipe-selector {
                grid-template-columns: 1fr;
            }
            
            .calculator-section {
                width: 95%;
                padding: 20px;
            }
        }
    </style>
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
            <li><a href="add-recipe.php">הוסף מתכון</a></li>
            <li><a href="recipe-calculator.php" class="active">מחשבון מתכונים</a></li>
            <li><a href="team.html">הצוות</a></li>
        </ul>
    </nav>
</header>

<div class="hero-title">
  <div class="hero-title-content">
    <h1>מחשבון מתכונים</h1>
    <p>
        בחר מתכון וסרגל את הכמויות לפי מספר המנות הרצוי.
    </p>
  </div>
</div>  

<main class="calculator-section">
    <form method="get" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: end; margin-bottom: 30px;">
        <div>
            <label for="recipe_id" style="font-weight: bold; color: #1B3C59; display: block; margin-bottom: 8px;">בחר מתכון:</label>
            <select id="recipe_id" name="recipe_id" required style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; width: 100%;">
                <option value="">-- בחר מתכון --</option>
                <?php foreach ($recipes as $r): ?>
                    <option value="<?= (int)$r["id"] ?>" <?= $recipeId == (int)$r["id"] ? "selected" : "" ?>>
                        <?= h($r["title"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="select-btn">חשב מתכון זה</button>
    </form>

    <?php if ($selectedRecipe && !empty($ingredients)): ?>
        <div class="recipe-details">
            <h2 class="recipe-title"><?= h($selectedRecipe["title"]) ?></h2>
            
            <div class="servings-control" id="servingsControl" data-base-servings="<?= h((string)$selectedRecipe["serving"]) ?>">
                <label for="servingsInput">חשב כמויות לפי מספר מנות</label>
                <div class="servings-control-actions">
                    <input
                        type="number"
                        id="servingsInput"
                        min="0.25"
                        step="0.25"
                        value="<?= $selectedRecipe["serving"] !== null ? (float)$selectedRecipe["serving"] : 1 ?>"
                    />
                    <button type="button" class="apply-btn" id="servingsApply">חשב</button>
                    <button type="button" class="reset-btn" id="servingsReset">איפוס</button>
                </div>
                <p class="servings-hint" id="servingsHint">
                    <?= $selectedRecipe["serving"] !== null ? "מבוסס על " . (float)$selectedRecipe["serving"] . " מנות" : "לא צוין מספר מנות, ברירת מחדל 1" ?>
                </p>
            </div>

            <h3 class="recipe-section-title">מצרכים</h3>
            <ul class="recipe-list" id="ingredientsList">
                <?php foreach ($ingredients as $ing): ?>
                    <?php $amount = trim((string)($ing["amount"] ?? "")) ?>
                    <li class="ingredient-item"
                        data-base-amount="<?= h($amount) ?>"
                        data-measurement="<?= h((string)($ing["measurement"] ?? "")) ?>"
                        data-name="<?= h((string)($ing["name"] ?? "")) ?>">
                        <?php if ($amount !== ""): ?>
                            <span class="ingredient-amount"><?= h($amount) ?></span>
                        <?php endif; ?>
                        <?php if (isset($ing["measurement"]) && $ing["measurement"] !== ""): ?>
                            <span class="ingredient-measurement"><?= h($ing["measurement"]) ?></span>
                        <?php endif; ?>
                        <span class="ingredient-name"><?= h((string)($ing["name"] ?? "")) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif ($recipeId > 0): ?>
        <div class="no-recipe">
            <p>המתכון לא נמצא או אין לו מצרכים.</p>
        </div>
    <?php else: ?>
        <div class="no-recipe">
            <p>בחר מתכון כדי להתחיל</p>
        </div>
    <?php endif; ?>
</main>

<footer>
    <div class="footer-content">
        <p>2026 The Flavor Forge &copy;</p>
        <p>פותח על ידי: <strong>נטלי, אדהם, מוראד</strong></p>
    </div>
</footer>

</script>
<script src="js/recipe-calculator.js"></script>
<script src="js/main.js"></script>
</body>
</html>
