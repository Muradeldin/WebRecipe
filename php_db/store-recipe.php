<?php
// store-recipe.php

declare(strict_types=1);

require __DIR__ . "/db.php"; // ← THIS is the key line

// ====== Helpers ======
function fail($msg) {
  header("Location: /add-recipe.php?err=" . urlencode($msg));
  exit;
}

$title = trim($_POST["title"] ?? "");
if ($title === "") fail("חסר שם מתכון.");
$serving = ($_POST["serving"] ?? "") !== "" ? (int)$_POST["serving"] : 2;
$difficultyId = ($_POST["difficulty_id"] ?? "") !== "" ? (int)$_POST["difficulty_id"] : null;
if ($difficultyId === null || $difficultyId <= 0) fail("חייב לבחור רמת קושי.");
$prepMinutes = ($_POST["prep_minutes"] ?? "") !== "" ? (int)$_POST["prep_minutes"] : 10;
$videoSrc = trim($_POST["video_src"] ?? "");
$imageSrc = trim($_POST["image_src"] ?? "");

$ingredients = $_POST["ingredients"] ?? [];
$steps = $_POST["steps"] ?? [];

$ingNames = $ingredients["name"] ?? [];
$ingAmounts = $ingredients['amount'] ?? [];
$ingMeasures = $ingredients['measurement'] ?? [];


$stepDescs = $steps["description"] ?? [];

$hasIngredient = false;
foreach ($ingNames as $n) {
  if (trim((string)$n) !== '') { $hasIngredient = true; break; }
}
if (!$hasIngredient) fail("חייב לפחות מצרך אחד.");

$hasStep = false;
foreach ($stepDescs as $s) {
  if (trim((string)$s) !== '') { $hasStep = true; break; }
}
if (!$hasStep) fail("חייב לפחות שלב אחד.");


try {
  $pdo->beginTransaction();

  // 1) Insert into recipes
  $stmtRecipe = $pdo->prepare("
    INSERT INTO recipes (title, serving, difficulty_id, prep_minutes, video_src, image_src)
    VALUES (:title, :serving, :difficulty_id, :prep_minutes, :video_src, :image_src)
  ");

  $stmtRecipe->execute([
    ":title" => $title,
    ":serving" => $serving,
    ":difficulty_id" => $difficultyId,
    ":prep_minutes" => $prepMinutes,
    ":video_src" => $videoSrc !== "" ? $videoSrc : null,
    ":image_src" => $imageSrc !== "" ? $imageSrc : null,
  ]);

  $recipeId = (int)$pdo->lastInsertId();

  // 2) Insert ingredients
  $stmtIng = $pdo->prepare("
    INSERT INTO recipe_ingredients
        (recipe_id, name, amount, measurement, ingredient_order)
    VALUES
        (:recipe_id, :name, :amount, :measurement, :ingredient_order)
    ");

    $order = 1;
    for ($i = 0; $i < count($ingNames); $i++) {
        $name = trim($ingNames[$i] ?? '');
        if ($name === '') continue;

        $amount = $ingAmounts[$i] ?? null;
        $measurement = trim($ingMeasures[$i] ?? '') ?: null;

        $stmtIng->execute([
            ':recipe_id' => $recipeId,
            ':name' => $name,
            ':amount' => $amount !== '' ? $amount : null,
            ':measurement' => $measurement,
            ':ingredient_order' => $order,
        ]);
        $order++;
    }



  // 3) Insert steps
  $stmtStep = $pdo->prepare("
    INSERT INTO recipe_steps (recipe_id, description, step_number)
    VALUES (:recipe_id, :description, :step_number)
  ");

  $stepNum = 1;
  for ($i = 0; $i < count($stepDescs); $i++) {
    $desc = trim($stepDescs[$i] ?? "");
    if ($desc === "") continue;

    $stmtStep->execute([
      ":recipe_id" => $recipeId,
      ":description" => $desc,
      ":step_number" => $stepNum,
    ]);
    $stepNum++;
  }


  $pdo->commit();

  header("Location: /add-recipe.php?ok=1");
  exit;

} catch (Exception $e) {
  if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
  fail("DB error");
}
