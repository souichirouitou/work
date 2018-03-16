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
    echo "database error";
    exit;
  }
}
 ?>

  <?php
  if(isset($_POST["id"])) {
    $id = $_POST["id"];
    $query = "SELECT member FROM allSchedule where id=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(array($id));
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $member_name[] = $row["member"];
    }

    try {
      foreach ((array)$member_name as $value) {
        $query = "DELETE FROM ".$value." WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute(array($id));
      };

      $query = "DELETE FROM allSchedule WHERE id = ?";
      $stmt = $pdo->prepare($query);
      $stmt->execute(array($id));
      header("Location: Main.php");
    } catch (PDOException $e) {
      header("Location: Logout.php");
      echo "database error";
      exit;
    }
  } else {
    echo "id がない";
  }

?>
