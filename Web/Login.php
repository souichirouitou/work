<?php
require 'password_compat-master/lib/password.php';
// セッション開始
session_start();

/* ログイン管理データベース */
$db['host'] = "***"; //DBサーバのURL
$db['user'] = "***"; // ユーザ名
$db['pass'] = "***"; // 上記ユーザのパスワード
$db['dbname'] = "***"; // データベース名

// エラーメッセージの初期化
$loginErrorMessage = "";
$singupErrorMessage = "";
$singupMessage = "";


// ログインボタンが押された場合
if (isset($_POST["login"])) {
    // 1. ユーザIDの入力チェック
    if (empty($_POST["userid"])) {  // emptyは値が空のとき
        $loginErrorMessage = 'ユーザーIDが未入力です。';
    } else if (empty($_POST["password"])) {
        $loginErrorMessage = 'パスワードが未入力です。';
    }

    if (!empty($_POST["userid"]) && !empty($_POST["password"])) {
        // 入力したユーザIDを格納
        $userid = $_POST["userid"];

        // 2. ユーザIDとパスワードが入力されていたら認証する
        $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);

        // 3. エラー処理
        try {
            $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

            $stmt = $pdo->prepare('SELECT * FROM userData WHERE name=? or number=?');
            $stmt->execute(array($userid, $userid));

            $password = $_POST["password"];

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $row['password'])) {
                    session_regenerate_id(true);

                    // 入力したIDのユーザー名を取得
                    $id = $row['id'];
                    $sql = "SELECT * FROM userData WHERE id = $id";  //入力したIDからユーザー名を取得
                    $stmt = $pdo->query($sql);
                    foreach ($stmt as $row) {
                        $row['name'];  // ユーザー名
                    }
                    $_SESSION["NAME"] = $row['name'];

                    header("Location: Main.php");  // メイン画面へ遷移
                    exit();  // 処理終了
                } else {
                    // 認証失敗
                    $loginErrorMessage = 'ユーザーIDあるいはパスワードに誤りがあります。1';
                }
            } else {
                $loginErrorMessage = 'ユーザーIDあるいはパスワードに誤りがあります。2';
            }
        } catch (PDOException $e) {
            $loginErrorMessage = 'データベースエラー';
        }
    }
}


// 登録ボタンが押された場合
if (isset($_POST["signUp"])) {
    // 学籍番号,ユーザ名,パスワードの入力チェック
    if (empty($_POST["studentnumber"])) {
        $singupErrorMessage = '学籍番号が未入力です';
    } else if (empty($_POST["username"])) {
        $singupErrorMessage = 'ユーザーIDが未入力です';
    } else if (empty($_POST["password3"])) {
        $singupErrorMessage = 'パスワードが未入力です';
    } else if (empty($_POST["password4"])) {
        $singupErrorMessage = 'パスワードが未入力です';
    }

    if (!empty($_POST["studentnumber"]) && !empty($_POST["username"]) && !empty($_POST["password3"]) && !empty($_POST["password4"]) && $_POST["password3"] === $_POST["password4"]) {
        // 入力した学籍番号,ユーザID,パスワードを格納
        $username = $_POST["username"];
        $password = $_POST["password3"];
        $studentnumber = $_POST["studentnumber"];

        // 学籍番号,ユーザID,パスワードが入力されていたら認証する
        $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);

        // 学籍番号チェック
        try {
            $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
            $stmt = $pdo->prepare('SELECT * FROM userData WHERE number = ?');
            $stmt->execute(array($studentnumber));

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stmt = $pdo->prepare("UPDATE userData set name=?,password=? where number=?");
                $stmt->execute(array($username, password_hash($password, PASSWORD_DEFAULT), $studentnumber));
                //$userid = $pdo->lastinsertid();  // 登録した(DB側でauto_incrementした)IDを$useridに入れる
                $singupMessage = '登録が完了しました。あなたの登録名は '. $username. ' です。パスワードは '. $password. ' です。';  // ログイン時に使用するIDとパスワード

                $stmt = $pdo->prepare('SELECT * FROM userData WHERE number = ?');
                $stmt->execute(array($studentnumber));

                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  $userid = $row['id'];
                } else {
                  echo "id取得失敗";
                  $singupErrorMessage('ああああ');
                }

                // スケジュール管理用のテーブル作成
                $db['host'] = "***"; //DBサーバのURL
                $db['user'] = "***"; // ユーザ名
                $db['pass'] = "***"; // 上記ユーザのパスワード
                $db['dbname'] = "***"; // データベース名
                $dsn2 = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
                try {
                  $pdo2 = new PDO($dsn2, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
                  $tablename = sprintf('%s',$username);
                  $query = "create table " .$tablename. " select * from mihon";
                  $pdo2->query($query);
                  //create table qwert (id int, startday varchar(10), stopday varchar(10), starttime varchar(5), stoptime varchar(5), schedule varchar(100), memo varchar(400) );
                  //create table a (id int, year varchar(4), month varchar(2), day varchar(2), hour varchar(2), starttime varchar(5), stoptime varchar(5), schedule varchar(100) );
                  //create table qwert(id int, year int, month int, day int, hour int, starttime int, stoptime int, schedule varchar(100) );
                } catch (PDOException $e) {
                  $singupErrorMessage = 'データベースエラー2';
                  // echo $e->getMessage();
                }


              } else {
                // 該当データなし
                $singupErrorMessage = '学籍番号に誤りがあります';
            }
        } catch (PDOException $e) {
            $singupErrorMessage = 'データベースエラー';
            // echo $e->getMessage();
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
    新規登録には、研究室に所属している学生の学生番号が必要です。<br>
    研究室所属で登録できない場合、連絡してください。
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
      tab3
    </div>
  </div><!-- tabbox -->
  </div>

<!-- ページを開いた際の最初に表示されるタブの選択 -->
<script type="text/javascript">
   ChangeTab('tab1');
</script>


<!--
  <footer class="footer">
    <div class="footer-inner">
      <p>Copyright &copy; Network Service Lab</p>
    </div>
  </footer>
-->

</body>
</html>
