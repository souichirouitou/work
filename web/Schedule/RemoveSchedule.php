<?php
// ログイン状態チェック
session_start();
if (!isset($_SESSION["NAME"])) {
  header("Location: Logout.php");
  exit();
} else {
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
    $query = "SELECT member FROM allSchedule where id=?";
    $pdo_schedule = new PDO($dsn_schedule, $db_schedule['user'], $db_schedule['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    $stmt = $pdo_schedule->prepare($query);
    $stmt->execute(array($id));
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $member_name[] = $row["member"];
    }
  } catch(PDOException $e) {
    header('Content-Type: text/plain; charset=UTF-8', true, 500);
    exit($e->getMessage());
  }

  try {
    $pdo_schedule = new PDO($dsn_schedule, $db_schedule['user'], $db_schedule['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    foreach ((array)$member_name as $value) {
      $value = $pdo_schedule->quote($value);
      $value = ltrim($value, '\'');
      $value = rtrim($value, '\'');
      $query = "DELETE FROM ".$value." WHERE id = ?";
      $stmt = $pdo_schedule->prepare($query);
      $stmt->execute(array($id));
    };
    $query = "DELETE FROM allSchedule WHERE id = ?";
    $stmt = $pdo_schedule->prepare($query);
    $stmt->execute(array($id));
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
