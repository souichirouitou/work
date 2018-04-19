<?php
// ログイン状態チェック
session_start();
if (!isset($_SESSION["NAME"])) {
  header("Location: Logout.php");
  exit();
} else {
  $username = $_SESSION["NAME"];
  require_once ('escape.php');
  require_once ('database_info.php');
  $username = $_SESSION["NAME"];
  $dsn_schedule = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db_schedule['host'], $db_schedule['dbname']);
}
?>

<?php
if(isset($_POST["id"])) {
  $id = $_POST["id"];
  try {
    $pdo_schedule = new PDO($dsn_schedule, $db_schedule['user'], $db_schedule['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    $username = $pdo_schedule->quote($username);
    $username = ltrim($username, '\'');
    $username = rtrim($username, '\'');
    $query = "DELETE FROM ".$username." WHERE id = ?";
    $stmt = $pdo_schedule->prepare($query);
    $stmt->execute(array($id));

    $query = "DELETE FROM allSchedule WHERE member = ? AND id = ?";
    $stmt = $pdo_schedule->prepare($query);
    $stmt->execute(array($username,$id));
    header("Location: Main.php");
    exit();
  } catch(PDOException $e) {
    header('Content-Type: text/plain; charset=UTF-8', true, 500);
    exit($e->getMessage());
  }
} else {
  echo "id がない";
  exit();
}
?>
