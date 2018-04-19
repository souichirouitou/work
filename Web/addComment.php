<?php
session_start();
if (!isset($_SESSION["NAME"])) {
  header("Location: Logout.php");
  exit();
} else {
  require_once ('escape.php');
  require_once ('database_info.php');
  $username = htmlspecialchars($_SESSION["NAME"], ENT_QUOTES, false);
  $dsn_schedule = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db_schedule['host'], $db_schedule['dbname']);
}
?>

<?php
if(isset($_POST["schedule_id"]) && $_POST["comment_text"] && $_POST["schedule_date"]) {
  $comment["schedule_id"] = $_POST["schedule_id"];
  $comment["schedule_date"] = $_POST["schedule_date"];
  $comment["text"] = $_POST["comment_text"];
  try {
    $pdo_schedule = new PDO($dsn_schedule, $db_schedule['user'], $db_schedule['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    $sql = "INSERT INTO comment(schedule_id, schedule_date, member, text) VALUES (?,?,?,?)";
    $stmt = $pdo_schedule->prepare($sql);
    $stmt->execute(array($comment["schedule_id"],$comment["schedule_date"],$username,$comment["text"]));
    header("Location: Main.php");
  } catch(PDOException $e) {
    header('Content-Type: text/plain; charset=UTF-8', true, 500);
    exit($e->getMessage());
  }
} else {
  echo "post error";
  exit();
}
?>
