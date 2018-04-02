<?php
session_start();
// ログイン状態チェック
if (!isset($_SESSION["NAME"])) {
  header("Location: Logout.php");
  exit;
} else {
  require_once ('escape.php');
  require_once ('database_info.php');
  $username = htmlspecialchars($_SESSION["NAME"], ENT_QUOTES);
  $dsn_schedule = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db_schedule['host'], $db_schedule['dbname']);
}
?>

<?php
/* 予定情報を格納 */
$week_name = array("日", "月", "火", "水", "木", "金", "土"); /* $week_name[$w] */
 if(isset($_POST["schedule"]) && isset($_POST["id"]) && isset($_POST["startday"]) && isset($_POST["stopday"])) {
   if(isset($_POST["starttime"]) && isset($_POST["stoptime"]) && isset($_POST["schedule_date"])) {
     $_POST = escape($_POST); // エスケープ処理
     $schedule_date = $_POST["schedule_date"];
     $temp = $_POST;
     $schedule = $_POST["schedule"];
     $id = $_POST["id"];
     $startday = $_POST["startday"];
     $stopday = $_POST["stopday"];
     $starttime = $_POST["starttime"];
     $stoptime = $_POST["stoptime"];
     $start_week_name = $week_name[date("w", strtotime($startday))];
     $stop_week_name = $week_name[date("w", strtotime($stopday))];
     if(isset($_POST["memo"])) {
       $memo = $_POST["memo"];
     }
   } else {
     echo "post error1"."<br>";
     exit;
   }
 } else {
   echo "post error2"."<br>";
   exit;
 }

 if(isset($_POST["tab_id"])) {
   $tab_id = $_POST["tab_id"];
   if($tab_id == 3) {
     if(isset($_POST["repeat_terms"]) && isset($_POST["terms_week"]) && isset($_POST["terms_weekname"]) && isset($_POST["terms_day"])) {
       $terms = array("repeat_terms"=>$_POST["repeat_terms"],"terms_week"=>$_POST["terms_week"],"terms_weekname"=>$_POST["terms_weekname"],"terms_day"=>$_POST["terms_day"]);
     } else {
       echo "terms error";
       exit;
     }
   }
 } else {
   echo "tab_id error";
   exit;
 }
?>

<?php
/* スケジュール参加者取得 */
try {
  $pdo = new PDO($dsn_schedule, $db_schedule['user'], $db_schedule['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
  $sql = "SELECT member FROM allSchedule WHERE id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array($id));
  while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row = escape($row); // エスケープ処理
    $member[] = $row["member"];
  }
} catch(PDOException $e) {
  header('Content-Type: text/plain; charset=UTF-8', true, 500);
  exit($e->getMessage());
}
?>

<?php
/* 表示用 */
$hiduke = "日付 :　".$startday."　(".$start_week_name.")　　〜　　".$stopday."　(".$stop_week_name.")";
$nitiji = "時間 :　".$starttime."　　〜　　".$stoptime."";
if($tab_id == 3) {
  $text = "条件 :　";
  if($terms["repeat_terms"] == 1) $text .= "毎日";
  else if($terms["repeat_terms"] == 2) $text .= "毎日(平日のみ)";
  else if($terms["repeat_terms"] == 3) {
    if($terms["terms_week"] == 1) $text .= "毎週";
    else if($terms["terms_week"] == 2) $text .= "毎月 第1";
    else if($terms["terms_week"] == 3) $text .= "毎月 第2";
    else if($terms["terms_week"] == 4) $text .= "毎月 第3";
    else if($terms["terms_week"] == 5) $text .= "毎月 第4";
    else if($terms["terms_week"] == 6) $text .= "毎月 最終";
    else $text = "terms_week error";
    if($terms["terms_weekname"] == "日") $text .= " 日曜日";
    else if($terms["terms_weekname"] == "月") $text .= " 月曜日";
    else if($terms["terms_weekname"] == "火") $text .= " 火曜日";
    else if($terms["terms_weekname"] == "水") $text .= " 水曜日";
    else if($terms["terms_weekname"] == "木") $text .= " 木曜日";
    else if($terms["terms_weekname"] == "金") $text .= " 金曜日";
    else if($terms["terms_weekname"] == "土") $text .= " 土曜日";
    else $test = "terms_weekname error";
  } else if($terms["repeat_terms"] == 4) {
    $text .= "毎月 " .$terms["terms_day"]. "日";
  }
}
?>


<?php
/* コメントフォーム作成 */
$comment_form = <<<EOD
  <div class="comment_form">
  <form name="comment_form" action="addComment.php" method="post">
  <textarea name="comment_text" class="comment_text" placeholder="400文字上限" rows="10" cols="100"></textarea>
  <input type="hidden" name="schedule_id" value=$id />
  <input type="hidden" name="schedule_date" value=$schedule_date />
  <br>
  <input type="submit" class="comment_button" name="comment_send" value="書き込み" style="width: 100px;" />
  </form>
  <br>
  </div>
EOD;
?>

<?php
/* コメント取得 */
$sql = "SELECT * FROM comment WHERE schedule_id = ? AND schedule_date = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(array($id,$schedule_date));
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  if(isset($row["member"]) && isset($row["text"])) {
    $row = escape($row); // エスケープ処理
    $comment_member = $row["member"];
    $comment_text = $row["text"];
    $comment[] = array("member"=>$comment_member, "text"=>$comment_text);
    $show_comment[] = "<div class=\"comment\">".htmlspecialchars($comment_member, ENT_QUOTES, false)."<br>".htmlspecialchars($comment_text, ENT_QUOTES, false)."</div>";
  }
}
?>

<!doctype html>
<html>
  <head>
  <title>予定確認</title>
  <meta charset="utf-8">
  <style type="text/css">
  .link_button {
    display       : inline-block;
    font-size     : 5pt;        /* 文字サイズ */
    text-align    : center;      /* 文字位置   */
    cursor        : pointer;     /* カーソル   */
    padding       : 16px 16px;   /* 余白       */
    background    : #000066;     /* 背景色     */
    color         : #ffffff;     /* 文字色     */
    transition    : .3s;         /* なめらか変化 */
    border        : 1px solid #000066;    /* 枠の指定 */
  }
  .link_button:hover{
    box-shadow    : none;        /* カーソル時の影消去 */
    color         : #000066;     /* 背景色     */
    background    : #ffffff;     /* 文字色     */
  }
  .comment_button {
    display       : inline-block;
    font-size     : 5pt;        /* 文字サイズ */
    text-align    : center;      /* 文字位置   */
    cursor        : pointer;     /* カーソル   */
    padding       : 6px 6px;   /* 余白       */
    background    : #000066;     /* 背景色     */
    color         : #ffffff;     /* 文字色     */
    transition    : .3s;         /* なめらか変化 */
    border        : 1px solid #000066;    /* 枠の指定 */
  }
  .comment_button:hover {
    box-shadow    : none;        /* カーソル時の影消去 */
    color         : #000066;     /* 背景色     */
    background    : #ffffff;     /* 文字色     */
  }
  .comment,.comment_form {
    border-bottom: solid 1px silver;
  }
  </style>
  <link rel="stylesheet" type="text/css" href="css/menu.css">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <link rel="stylesheet" type="text/css" href="css/item.css">
  <link rel="stylesheet" type="text/css" href="css/schedule.css">
  <link rel="stylesheet" type="text/css" href="css/panmenu.css">
  <link href="http://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css" rel="stylesheet">
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
  <script type="text/javascript">
  function change() {
    $('#changeSchedule').append("<input type=\"hidden\" name=\"function\" value=\"change\" />");
    $('#changeSchedule').append("<input type=\"hidden\" name=\"id\" value=\"" +<?php echo $id; ?>+ "\" />");
    document.getElementById('changeSchedule').action="ChangeSchedule.php";
    document.changeSchedule.submit();
  }
  function remove() {
    text = "この予定を削除しますか？";
    if(window.confirm(text)){
      $('#changeSchedule').append("<input type=\"hidden\" name=\"function\" value=\"delete\" />");
      $('#changeSchedule').append("<input type=\"hidden\" name=\"id\" value=\"" +<?php echo $id; ?>+ "\" />");
      document.getElementById('changeSchedule').action="RemoveSchedule.php";
      document.changeSchedule.submit();
    }	else{
      window.alert('キャンセルされました'); // 警告ダイアログを表示
    }
  }
  function leave() {
    text = "この予定から抜けますか？";
    if(window.confirm(text)){
      $('#changeSchedule').append("<input type=\"hidden\" name=\"function\" value=\"leave\" />");
      $('#changeSchedule').append("<input type=\"hidden\" name=\"id\" value=\"" +<?php echo $id; ?>+ "\" />");
      document.getElementById('changeSchedule').action="LeaveSchedule.php";
      document.changeSchedule.submit();
    }	else{
      window.alert('キャンセルされました'); // 警告ダイアログを表示
    }
  }
  </script>
</head>
<body>

<div class="bg-menu">
<div class="container">
  <!-- メニュー部分ここから -->
  <label for="menuOn">
    <input id="menuOn" type="checkbox">
    <menu>
      <ul>
        <!--<li><a href="#menu1">登録内容変更</a>-->
        <li><a href="Logout.php">ログアウト</a>
      </ul>
    </menu>
    <div class="overlay"></div>
  </label>
  <!-- メニュー部分ここまで -->
</div>
</div>

<div class="item">
  <div class="message">
    <div class="image__item">
      <!--<a href="Main.php"><img src="image/message.png"></a>-->
      <input type="button" class="link_button" onclick="location.href='Main.php'"value="マイページ" />
    </div>
  </div>
  <div class="file">
    <div class="image__item">
      <input type="button" class="link_button" onclick="location.href='Main_member.php'"value="全体スケジュール" />
      <!--<a href="Main.php"><img src="image/message.png"></a>-->
    </div>
  </div>
</div>

<!-- パンくずメニュー(予定) -->
<div class="pan_menu">
  <div class="pan1">
    マイページ -> 予定の確認
  </div>
</div>

<div class="box__main">
    <div class="box__schedule">
      <div class="box__schedule_title">
        <b>スケジュール</b>
      </div>
      <div class="box__schedule_day">
        <?php
        echo "<h2>" .htmlspecialchars($schedule, ENT_QUOTES, false). "</h2>";
        ?>
        <form name="changeSchedule" id="changeSchedule" action="" method="post">
          <input type="button" onClick="change()" value="変更" />
          <input type="button" onClick="remove()" value="削除" />
          <input type="button" onClick="leave()" value="この予定から抜ける" />
        </form>
      </div>
      <div class="box__schedule_view">
        <table width="100%" margin-bottom:"1%">
          <tr>
            <td width="12%" class="schedule__info">日時</td>
            <td class="schedule__content">
              <?php
              echo htmlspecialchars($hiduke, ENT_QUOTES, false);
              echo "<br>";
              echo htmlspecialchars($nitiji, ENT_QUOTES, false);
              echo "<br>";
              if($tab_id==3) echo htmlspecialchars($text, ENT_QUOTES, false);
              ?>
            </td>
          </tr>

          <tr>
            <td class="schedule__info">予定</td>
            <td class="schedule__content">
              <?= htmlspecialchars($schedule, ENT_QUOTES, false) ?>
            </td>
          </tr>

          <tr>
            <td class="schedule__info">参加者</td>
            <td class="schedule__content">
              <?php
              foreach ($member as $value) {
                echo "・".htmlspecialchars($value, ENT_QUOTES, false)."　";
              }
              ?>
            </td>
          </tr>

          <tr>
            <td class="schedule__info">連絡事項</td>
            <td class="schedule__content">
              <?= htmlspecialchars($memo, ENT_QUOTES, false) ?>
            </td>
          </tr>

          <tr>
            <td class="schedule__info">コメント</td>
            <td class="schedule__content">
              <?php
              echo $comment_form;
              if(isset($show_comment)) {
                foreach ($show_comment as $value) {
                  echo $value;
                }
              }
              ?>
            </td>
          </tr>

        </table>
      </div>
    </div>
</div>

</body>
</html>
