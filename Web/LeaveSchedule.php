<?php
session_start();

// ログイン状態チェック
if (!isset($_SESSION["NAME"])) {
  header("Location: Logout.php");
  exit;
} else {
  $username = $_SESSION["NAME"];
  /* スケジュール管理データベース */
  $db['host'] = "***"; //DBサーバのURL
  $db['user'] = "***"; // ユーザ名
  $db['pass'] = "***"; // 上記ユーザのパスワード
  $db['dbname'] = "***"; // データベース名
  $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']); // 認証
  try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
  } catch (PDOException $e) {
    header("Location: Logout.php");
    exit;
    echo "database error1";
  }
}
 ?>

  <?php
  if(isset($_POST["id"])) {
    $id = $_POST["id"];
    try {
      $query = "DELETE FROM ".$username." WHERE id = ?";
      $stmt = $pdo->prepare($query);
      $stmt->execute(array($id));

      $query = "DELETE FROM allSchedule WHERE member = ? AND id = ?";
      $stmt = $pdo->prepare($query);
      $stmt->execute(array($username,$id));
      header("Location: Main.php");
    } catch (PDOException $e) {
      echo "database error2";
      header("Location: Logout.php");
      exit;
    }
  } else {
    echo "id がない";
  }
?>
