<?php
session_start();

// ログイン状態チェック
if (!isset($_SESSION["NAME"])) {
  header("Location: Logout.php");
  exit;
}

/* スケジュール管理データベース */
$db['host'] = "***"; //DBサーバのURL
$db['user'] = "***"; // ユーザ名
$db['pass'] = "***"; // 上記ユーザのパスワード
$db['dbname'] = "***"; // データベース名
$dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
$pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
?>

<?php
$member = array();
$week_name = array("日", "月", "火", "水", "木", "金", "土"); /* $week_name[$w] */
/* 予定情報を格納 */
 if(isset($_POST["schedule"]) && isset($_POST["id"]) && isset($_POST["startday"]) && isset($_POST["stopday"])) {
   if(isset($_POST["starttime"]) && isset($_POST["stoptime"])) {
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
     header("Location: Logout.php");
     exit;
   }
 } else {
   echo "post error2"."<br>";
   header("Location: Logout.php");
   exit;
 }

 if(isset($_POST["tab_id"])) {
   $tab_id = $_POST["tab_id"];
   if($tab_id == 3) {
     if(isset($_POST["repeat_terms"]) && isset($_POST["terms_week"]) && isset($_POST["terms_weekname"]) && isset($_POST["terms_day"])) {
       $terms = array("repeat_terms"=>$_POST["repeat_terms"],"terms_week"=>$_POST["terms_week"],"terms_weekname"=>$_POST["terms_weekname"],"terms_day"=>$_POST["terms_day"]);
     } else {
       echo "terms error";
     }
   }
 } else {
   echo "tab_id error";
 }
 //var_dump($terms);

 $sql = "SELECT member FROM allSchedule WHERE id = ?";
 $stmt = $pdo->prepare($sql);
 $stmt->execute(array($id));
 while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
   $member[] = $row["member"];
 }
?>

<?php
date_default_timezone_set('Asia/Tokyo'); // タイムゾーン
$year = date("Y");
$month = date("m");
$day = date("d");
$date = $year."-".$month."-".$day;
$w = date("w");
$week_name = array("日", "月", "火", "水", "木", "金", "土"); /* $week_name[$w] */
$mFlag = -1; // 現在の月が何日まであるか
$lFlag = -1; // うるう年かどうか
$searchday = array();
for($i=0; $i<7; $i++) {
  $plus_day = "+".$i." day";
  $searchday[] = date("Y", strtotime($plus_day))."-".date("m", strtotime($plus_day))."-".date("d", strtotime($plus_day));
}

/* うるう年計算 */
if( ($year%4)==0 ){
  if( ($year%100)==0 ) {
    if( ($year%400)==0 ) {
      $lFlag = 1;
    } else { $lFlag = 0; }
  } else { $lFlag = 1; }
} else { $lFlag = 0; }

/** 何日まであるか計算 **/
if( ($month=='04') || ($month=='06') || ($month=='09') || ($month=='11') ){
  $mFlag = 30;
} else if( $month == '02' ) {
    if( $lFlag == 1) {
      $mFlag = 29;
    } else if( $lFlag == 0) {
      $mFlag = 28;
    } else {
      echo "計算エラー";
    }
} else {
    $mFlag = 31;
}
?>

<!doctype html>
<html>
  <head>
  <title>予定確認</title>
  <meta charset="utf-8">
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
      <input type="button" onclick="location.href='Main.php'"value="マイページ" style="font-size: 2em;">
    </div>
  </div>
  <div class="file">
    <div class="image__item">
      <input type="button" onclick="location.href='Main_member.php'"value="全体スケジュール" style="font-size: 2em;">
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
        echo "<h2>" .$schedule. "</h2>";
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
              $hiduke = "日付 :　".$startday."　(".$start_week_name.")　　〜　　".$stopday."　(".$stop_week_name.")<br>";
              $nitiji = "時間 :　".$starttime."　　〜　　".$stoptime."<br>";
              echo $hiduke;
              echo $nitiji;
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
                  else echo "terms_week error";
                  if($terms["terms_weekname"] == "日") $text .= " 日曜日";
                  else if($terms["terms_weekname"] == "月") $text .= " 月曜日";
                  else if($terms["terms_weekname"] == "火") $text .= " 火曜日";
                  else if($terms["terms_weekname"] == "水") $text .= " 水曜日";
                  else if($terms["terms_weekname"] == "木") $text .= " 木曜日";
                  else if($terms["terms_weekname"] == "金") $text .= " 金曜日";
                  else if($terms["terms_weekname"] == "土") $text .= " 土曜日";
                  else echo "terms_weekname error";
                } else if($terms["repeat_terms"] == 4) {
                  $text .= "毎月 " .$terms["terms_day"]. "日";
                }
                echo $text;
                //var_dump($text);
              }
              ?>
            </td>
          </tr>

          <tr>
            <td class="schedule__info">予定</td>
            <td class="schedule__content">
              <?php
              echo $schedule;
              ?>
            </td>
          </tr>

          <tr>
            <td class="schedule__info">メモ</td>
            <td class="schedule__content">
              <?php
              echo $memo;
              ?>
            </td>
          </tr>

          <tr>
            <td class="schedule__info">参加者</td>
            <td class="schedule__content">
              <?php
              foreach ($member as $value) {
                echo "・".$value."　";
              }
              ?>
            </td>
          </tr>

          <tr>
            <td class="schedule__info">コメント</td>
            <td class="schedule__content"></td>
          </tr>

        </table>
      </div>
    </div>
</div>

</body>
</html>
