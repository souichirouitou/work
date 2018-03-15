<?php
session_start();

if (isset($_SESSION["NAME"])) {
    $errorMessage = "ログアウトしました。";
} else {
  $errorMessage = "セッションがタイムアウトしました。";
}

// セッションの変数のクリア
$_SESSION = array();

// セッションクリア
@session_destroy();
?>


<!doctype html>
<html>
  <head>
      <meta charset="UTF-8">
      <title>ログアウト</title>
  </head>
  <body>
    <h1>ログアウト</h1>
    <div><?php htmlspecialchars($errorMessage, ENT_QUOTES); ?></div>
    <ul>
        <li><a href="Login_main.php">ログイン画面に戻る</a></li>
    </ul>
  </body>
</html>
