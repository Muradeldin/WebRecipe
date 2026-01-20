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

// Handle file upload
$uploadedImagePath = null;
if (isset($_FILES["fileToUpload"]) && $_FILES["fileToUpload"]["error"] === UPLOAD_ERR_OK) {
  $target_dir = __DIR__ . "/../images/recipe_img/";
  
  // Create directory if it doesn't exist
  if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
  }
  
  $imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
  
  // Sanitize recipe title for filename
  $sanitizedTitle = preg_replace('/[^א-תa-z0-9]/i', '_', $title);
  $sanitizedTitle = preg_replace('/_+/', '_', $sanitizedTitle);
  $sanitizedTitle = trim($sanitizedTitle, '_');
  
  // Create filename using recipe title
  $fileName = $sanitizedTitle . '.' . $imageFileType;
  $target_file = $target_dir . $fileName;
  
  // Check if image file is actual image
  $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  if ($check === false) {
    fail("הקובץ שהועלה אינו תמונה.");
  }
  
  // Check file size (5MB max)
  if ($_FILES["fileToUpload"]["size"] > 5000000) {
    fail("הקובץ גדול מדי. מקסימום 5MB.");
  }
  
  // Allow certain file formats
  if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif", "webp"])) {
    fail("רק קבצי JPG, JPEG, PNG, GIF ו-WEBP מותרים.");
  }
  
  // Try to upload file (overwrite if exists)
  if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    fail("שגיאה בהעלאת הקובץ.");
  }
}

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
    INSERT INTO recipes (title, serving, difficulty_id, prep_minutes, video_src)
    VALUES (:title, :serving, :difficulty_id, :prep_minutes, :video_src)
  ");

  $stmtRecipe->execute([
    ":title" => $title,
    ":serving" => $serving,
    ":difficulty_id" => $difficultyId,
    ":prep_minutes" => $prepMinutes,
    ":video_src" => $videoSrc !== "" ? $videoSrc : null,
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
