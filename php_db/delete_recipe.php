<?php
// php_db/delete_recipe.php
declare(strict_types=1);

require __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  exit("Method Not Allowed");
}

$id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
if ($id <= 0) {
  http_response_code(400);
  exit("Invalid recipe id");
}

try {
  $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
  $stmt->execute([$id]);

  if ($stmt->rowCount() === 0) {
    http_response_code(404);
    exit("Recipe not found");
  }
} catch (PDOException $e) {
  http_response_code(500);
  exit("DB error: " . $e->getMessage());
}

// Go back to recipes list
header("Location: ../recipes.php");
exit;
