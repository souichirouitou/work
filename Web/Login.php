<?php
require_once ('escape.php');
require_once ('database_info.php');
require 'password_compat-master/lib/password.php';
session_start();
$dsn_schedule = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db_schedule['host'], $db_schedule['dbname']);
$dsn_login = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db_login['host'], $db_login['dbname']);
?>

<?php
$loginErrorMessage = "";
$singupErrorMessage = "";
$singupMessage = "";
?>

<?php
/* ログイン処理 */
if (isset($_POST["login"])) {
  if (empty($_POST["userid"])) {
    $loginErrorMessage = 'ユーザーIDが未入力です。';
  } else if (empty($_POST["password"])) {
    $loginErrorMessage = 'パスワードが未入力です。';
  }

  if (!empty($_POST["userid"]) && !empty($_POST["password"])) {
    try {
      $password = $_POST["password"];
      $userid = $_POST["userid"];
      //$_POST = escape($_POST);
      $pdo_login = new PDO($dsn_login, $db_login['user'], $db_login['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
      $stmt = $pdo_login->prepare('SELECT * FROM userData WHERE name=? or number=?');
      $stmt->execute(array($userid, $userid));
      if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $row['password'])) {
          session_regenerate_id(true);
          $id = $row['id'];
          $sql = "SELECT * FROM userData WHERE id = ?";
          $stmt = $pdo_login->prepare($sql);
          $stmt->execute(array($id));
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(isset($row["name"])) $_SESSION["NAME"] = $row['name'];
          }
          header("Location: Main.php");
          exit();
        } else {
          $loginErrorMessage = 'ユーザーIDあるいはパスワードに誤りがあります。1';
        }
      } else {
        $loginErrorMessage = 'ユーザーIDあるいはパスワードに誤りがあります。2';
      }
    } catch(PDOException $e) {
      header('Content-Type: text/plain; charset=UTF-8', true, 500);
      exit($e->getMessage());
    }
  }
}

/* 登録処理 */
if (isset($_POST["signUp"])) {
  if (empty($_POST["studentnumber"])) {
      $singupErrorMessage = '学籍番号が未入力です';
  } else if (empty($_POST["username"])) {
      $singupErrorMessage = 'ユーザーIDが未入力です';
  } else if (empty($_POST["password3"])) {
      $singupErrorMessage = 'パスワードが未入力です';
  } else if (empty($_POST["password4"])) {
      $singupErrorMessage = 'パスワードが未入力です';
  }

  if (isset($_POST["studentnumber"]) && isset($_POST["username"]) && isset($_POST["password3"]) && isset($_POST["password4"]) && $_POST["password3"] === $_POST["password4"]) {
    $username = $_POST["username"];
    $password = $_POST["password3"];
    $studentnumber = $_POST["studentnumber"];
    try {
      $pdo_login = new PDO($dsn, $db_login['user'], $db_login['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
      $stmt = $pdo->prepare('SELECT * FROM userData WHERE number = ?');
      $stmt->execute(array($studentnumber));
      if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt = $pdo->prepare("UPDATE userData set name=?,password=? where number=?");
        $stmt->execute(array($username, password_hash($password, PASSWORD_DEFAULT), $studentnumber));
        $singupMessage = '登録が完了しました。あなたの登録名は '.htmlspecialchars($username, ENT_QUOTES, false).' です。パスワードは '.htmlspecialchars($password, ENT_QUOTES, false).' です。';
        $stmt = $pdo->prepare('SELECT * FROM userData WHERE number = ?');
        $stmt->execute(array($studentnumber));
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $userid = $row['id'];
        } else {
          $singupErrorMessage('ID取得失敗');
        }

        try {
          $pdo_schedule = new PDO($dsn_schedule, $db_schedule['user'], $db_schedule['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
          $tablename = sprintf('%s',$username);
          $tablename = $pdo_schedule->quote($tablename);
          $tablename = ltrim($tablename, '\'');
          $tablename = rtrim($tablename, '\'');
          $query = "create table " .$tablename. " select * from mihon";
          $pdo_schedule->query($query);
        } catch(PDOException $e) {
          header('Content-Type: text/plain; charset=UTF-8', true, 500);
          exit($e->getMessage());
        }
      } else {
        $singupErrorMessage = '学籍番号に誤りがあります';
      }
    } catch(PDOException $e) {
      header('Content-Type: text/plain; charset=UTF-8', true, 500);
      exit($e->getMessage());
    }
  } else if($_POST["password3"] != $_POST["password4"]) {
      $singupErrorMessage = 'パスワードに誤りがあります。';
  }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
  <link rel="stylesheet" type="text/css" href="css_2/message.css">
  <link rel="stylesheet" type="text/css" href="css_2/login_tab.css">
  <link rel="stylesheet" type="text/css" href="css_2/schedule.css">
  <script src="js/function.js" language="JavaScript" type="text/javascript"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.4.1/css/swiper.min.css">
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
  <title>研究室</title>
  <script type="text/javascript">
  </script>
</head>
<body>
  <div class="box__area">
    <div class="box__logo">
      <h1> NetWork Service Lab </h1>
    </div>
  </div>

  <div class="box__area2">
    <article class="research">
      <h1> ログインページ </h1>
    </article>
  </div>

  <div class="box__item">
    <h2> 情報 </h2>
  </div>

  <div class="box__area3">
    研究室用
  </div>

  <div class="box__item">
    <h2> ログイン・新規登録 </h2>
  </div>

  <div class="box__area4">
    <div class="tabbox">
    <p class="tabs">
      <a href="#tab1" class="tab1" onclick="ChangeTab('tab1'); return false;">ログイン</a>
      <a href="#tab2" class="tab2" onclick="ChangeTab('tab2'); return false;">新規登録</a>
    </p>
    <div id="tab1" class="tab">
      <div class="schedule__day">
        <form name="loginForm" action="" method="POST">
          <div class="login__field">
            <fieldset>
              <legend>ログインフォーム</legend>
              <div><font color="#ff0000"><?php echo htmlspecialchars($singupErrorMessage, ENT_QUOTES); ?></font></div>
              <div><font color="#0000ff"><?php echo htmlspecialchars($singupMessage, ENT_QUOTES); ?></font></div>
              <div><font color="#ff0000"><?php echo htmlspecialchars($loginErrorMessage, ENT_QUOTES); ?></font></div>
              <label for="userid">ユーザーID</label><input type="text" id="userid" name="userid" size="50" placeholder="学籍番号 or 登録名を入力" value="<?php if (!empty($_POST["userid"])) {echo htmlspecialchars($_POST["userid"], ENT_QUOTES);} ?>">
              <br>
              <label for="password">パスワード</label><input type="password" id="password" name="password" value="" size="50" placeholder="パスワードを入力">
              <br>
              <input type="submit" id="login" name="login" value="ログイン">
            </fieldset>
        </div>
        </form>
      </div>
    </div>

    <div id="tab2" class="tab">
      <div class="schedule__day">
        <form name="loginForm" action="" method="POST">
          <fieldset>
            <legend>新規登録フォーム</legend>
            <div><font color="#ff0000"><?php echo htmlspecialchars($singupErrorMessage, ENT_QUOTES); ?></font></div>
            <div><font color="#0000ff"><?php echo htmlspecialchars($singupMessage, ENT_QUOTES); ?></font></div>
            <label for="studentnumber">学籍番号</label><input type="text" id="studentnumber" size="50" name="studentnumber" placeholder="学籍番号を入力" value="<?php if (!empty($_POST["studentnumber"])) {echo htmlspecialchars($_POST["studentnumber"], ENT_QUOTES);} ?>">
            <br>
            <label for="username">ユーザー名</label><input type="text" id="username" size="50" name="username" placeholder="ユーザー名を入力(数字以外)" value="<?php if (!empty($_POST["username"])) {echo htmlspecialchars($_POST["username"], ENT_QUOTES);} ?>">
            <br>
            <label for="password3">パスワード</label><input type="password" id="password3" size="50" name="password3" value="" placeholder="パスワードを入力">
            <br>
            <label for="password4">パスワード(確認用)</label><input type="password" id="password4" size="50" name="password4" value="" placeholder="再度パスワードを入力">
            <br>
            <input type="submit" id="signUp" name="signUp" value="新規登録">
          </fieldset>
        </form>
      </div>
    </div>

    <div id="tab3" class="tab">
    </div>
  </div>
  </div>

<script type="text/javascript">
   ChangeTab('tab1');
</script>
</body>
</html>
