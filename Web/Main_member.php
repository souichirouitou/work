<?php
// ログイン状態チェック
session_start();
if (!isset($_SESSION["NAME"])) {
  header("Location: Logout.php");
  exit();
} else {
  require_once ('escape.php');
  require_once ('database_info.php');
  $dsn_schedule = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db_schedule['host'], $db_schedule['dbname']);
  $dsn_login = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db_login['host'], $db_login['dbname']);
  if(isset($_POST["addCount"])) $addCount = $_POST["addCount"];
  else $addCount = 0;
  if(isset($_POST["redCount"])) $redCount = $_POST["redCount"];
  else $redCount = 0;
}
?>

<?php
date_default_timezone_set('Asia/Tokyo'); // タイムゾーン
define("WEEK_NUM", 1);
$next_week = 7 * 24 * 60 * 60 * $addCount;
$prev_week = -1 * 7 * 24 * 60 * 60 * $redCount;
$show_week = $next_week;
$week_name = array("日", "月", "火", "水", "木", "金", "土"); /* $week_name[$w] */
$mFlag = -1; // 現在の月が何日まであるか
$lFlag = -1; // うるう年かどうか
for($i=0; $i<7; $i++) {
  $plus_day = "+".$i." day";
  $year_array[] = date("Y", strtotime($plus_day)+$show_week);
  $month_array[] = date("m", strtotime($plus_day)+$show_week);
  $day_array[] = date("d", strtotime($plus_day)+$show_week);;
  $week_array[] = date("w", strtotime($plus_day)+$show_week);
  $searchday[] = $year_array[$i]."-".$month_array[$i]."-".$day_array[$i];
  $day_figure[] = $year_array[$i].$month_array[$i].$day_array[$i];
  $show_day[] =  ($day_array[$i]). " (" .$week_name[$week_array[$i]]. ")";
}

/* うるう年計算 */
if( ($year_array[0]%4)==0 ){
  if( ($year_array[0]%100)==0 ) {
    if( ($year_array[0]%400)==0 ) {
      $lFlag = 1;
    } else { $lFlag = 0; }
  } else { $lFlag = 1; }
} else { $lFlag = 0; }

/** 何日まであるか計算 **/
if( ($month_array[0]=='04') || ($month_array[0]=='06') || ($month_array[0]=='09') || ($month_array[0]=='11') ){
  $mFlag = 30;
} else if( $month_array[0] == '02' ) {
  if( $lFlag == 1) {
    $mFlag = 29;
  } else if( $lFlag == 0) {
    $mFlag = 28;
  } else {
    echo "計算エラー";
    exit();
  }
} else {
  $mFlag = 31;
}
?>

<?php
/* ログイン者のアカウントID取得 */
try {
  $pdo_login = new PDO($dsn_login, $db_login['user'], $db_login['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
  $query = "SELECT name,id from userData Where name = ?";
  $stmt = $pdo_login->prepare($query);
  $stmt->execute(array($_SESSION["NAME"]));
  while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if(isset($row["name"]) && isset($row["id"])) {
      $row = escape($row);
      $user_name = $row["name"];
      $user_id = $row["id"];
    }
  }
} catch(PDOException $e) {
  header('Content-Type: text/plain; charset=UTF-8', true, 500);
  exit($e->getMessage());
}
?>

<?php
/* ログイン者以外の登録者を取得 */
try {
  $query = "SELECT name,id from userData Where name != ?";
  $stmt = $pdo_login->prepare($query);
  $stmt->execute(array($_SESSION["NAME"]));
  while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if(isset($row["name"]) && isset($row["id"])) {
      escape($row);
      $member_name[] = $row["name"];
      $member_id[$row["name"]] = $row["id"];
    }
  }
} catch(PDOException $e) {
  header('Content-Type: text/plain; charset=UTF-8', true, 500);
  exit($e->getMessage());
}

$membername_count = count($member_name);
foreach ($member_name as $value) {
$form[] = <<<EOD
  <tr>
    <td>$value</td>
    <td id="schedule1_$value">
      <!-- スケジュール取得 -->
      <!-- 予定登録 -->
      <form action="Schedule.php" method="POST">
        <input type="hidden" name="date" value="$searchday[0]" />
        <input type="submit" name="link" value="予定を登録" />
      </form>
    </td>

    <td id="schedule2_$value">
      <!-- スケジュール取得 -->
      <!-- 予定登録 -->
      <form action="Schedule.php" method="POST">
        <input type="hidden" name="date" value="$searchday[1]" />
        <input type="submit" name="link" value="予定を登録" />
      </form>
    </td>

    <td id="schedule3_$value">
      <!-- スケジュール取得 -->
      <!-- 予定登録 -->
      <form action="Schedule.php" method="POST">
        <input type="hidden" name="date" value="$searchday[2]" />
        <input type="submit" name="link" value="予定を登録" />
      </form>
    </td>

    <td id="schedule4_$value">
      <!-- スケジュール取得 -->
      <!-- 予定登録 -->
      <form action="Schedule.php" method="POST">
        <input type="hidden" name="date" value="$searchday[3]" />
        <input type="submit" name="link" value="予定を登録" />
      </form>
    </td>

    <td id="schedule5_$value">
      <!-- スケジュール取得 -->
      <!-- 予定登録 -->
      <form action="Schedule.php" method="POST">
        <input type="hidden" name="date" value="$searchday[4]" />
        <input type="submit" name="link" value="予定を登録" />
      </form>
    </td>

    <td id="schedule6_$value">
      <!-- スケジュール取得 -->
      <!-- 予定登録 -->
      <form action="Schedule.php" method="POST">
        <input type="hidden" name="date" value="$searchday[5]" />
        <input type="submit" name="link" value="予定を登録" />
      </form>
    </td>

    <td id="schedule7_$value">
      <!-- スケジュール取得 -->
      <!-- 予定登録 -->
      <form action="Schedule.php" method="POST">
        <input type="hidden" name="date" value="$searchday[6]" />
        <input type="submit" name="link" value="予定を登録" />
      </form>
    </td>
  </tr>
EOD;
}
?>



<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
  <title>main page</title>
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
  </style>
  <link rel="stylesheet" type="text/css" href="css/menu.css">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <link rel="stylesheet" type="text/css" href="css/item.css">
  <link href="http://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css" rel="stylesheet">
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
  <script src="js/function.js" language="JavaScript" type="text/javascript"></script>
  <script type="text/javascript">
  function addCountSend() {
    $('#countSend').append("<input type=\"hidden\" name=\"addCount\" value=\"" + <?php echo $addCount+1 ?> + "\" />");
    document.showWeek.submit();
  }
  function redCountSend() {
    $('#countSend').append("<input type=\"hidden\" name=\"addCount\" value=\"" + <?php echo $addCount-1 ?> + "\" />");
    document.showWeek.submit();
  }
  </script>
  <script type="text/javascript">
  function view1(add_id,check_schedule,schedule_id,schedule,startday,stopday,starttime,stoptime,memo,repeat_terms,terms_week,terms_weekname,terms_day,tab_id,searchday) {
    var form_text = "<form name=\""+check_schedule+"\" method=\"POST\" action=\"ConfirmSchedule.php\">";
    form_text += "<input type=\"hidden\" name=\"id\" value=\""+schedule_id+"\" />";
    form_text += "<input type=\"hidden\" name=\"schedule\" value=\""+schedule+"\" />";
    form_text += "<input type=\"hidden\" name=\"startday\" value=\""+startday+"\" />";
    form_text += "<input type=\"hidden\" name=\"stopday\" value=\""+stopday+"\" />";
    form_text += "<input type=\"hidden\" name=\"starttime\" value=\""+starttime+"\" />";
    form_text += "<input type=\"hidden\" name=\"stoptime\" value=\""+stoptime+"\" />";
    form_text += "<input type=\"hidden\" name=\"memo\" value=\""+memo+"\" />";
    form_text += "<input type=\"hidden\" name=\"tab_id\" value=\""+tab_id+"\" />";
    form_text += "<input type=\"hidden\" name=\"repeat_terms\" value=\""+repeat_terms+"\" />";
    form_text += "<input type=\"hidden\" name=\"terms_week\" value=\""+terms_week+"\" />";
    form_text += "<input type=\"hidden\" name=\"terms_weekname\" value=\""+terms_weekname+"\" />";
    form_text += "<input type=\"hidden\" name=\"terms_day\" value=\""+terms_day+"\" />";
    form_text += "<input type=\"hidden\" name=\"schedule_date\" value=\""+searchday+"\" />";
    form_text += "<a href=\"javascript:document."+check_schedule+".submit()\">"+schedule+"</a><br>";
    form_text += "</form>";
    $(add_id).append(form_text);
  }
  </script>
  <script type="text/javascript">
    window.onload = function(){
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
      <input type="button" class="link_button" onclick="location.href='Main.php'"value="マイページ" />
    </div>
  </div>
  <div class="file">
    <div class="image__item">
      <input type="button" class="link_button" onclick="location.href='Main_member.php'"value="全体スケジュール" />
    </div>
  </div>
</div>

<div class="box__main">
  <div class="box__schedule">
    <div class="box__schedule_title">
      <b>スケジュール</b>
    </div>
    <div class="box__schedule_day">
      <?php
      echo "<h3>" .$year_array[0]. "年" .$month_array[0]. "月" .$day_array[0]. "日 (" .$week_name[$week_array[0]]. ") </h3>";
      ?>
      <form name="showWeek" id="countSend" action="" method="post">
        <input type="button" onClick="redCountSend()" value="前の週を見る" />
        <input type="button" onClick="addCountSend()" value="次の週を見る" />
      </form>
    </div>

    <div class="box_schedule_main">
      <table width="100%" margin-bottom:"1%">

        <tr style="background-color: #E6E6E6;">
          <td width="9%"></td>
          <td id="day1"  width="13%">
            <?= $show_day[0] ?>
          </td>
          <td id="day2" width="13%">
            <?= $show_day[1] ?>
          </td>
          <td id="day3" width="13%">
            <?= $show_day[2] ?>
          </td>
          <td id="day4" width="13%">
            <?= $show_day[3] ?>
          </td>
          <td id="day5" width="13%">
            <?= $show_day[4] ?>
          </td>
          <td id="day6" width="13%">
            <?= $show_day[5] ?>
          </td>
          <td id="day7" width="13%">
            <?= $show_day[6] ?>
          </td>
        </tr>

        <tr>
          <td><?php echo htmlspecialchars($_SESSION["NAME"], ENT_QUOTES); ?></td>
          <td id="schedule1">
            <form action="Schedule.php" method="POST">
              <input type="hidden" name="date" value="<?= $searchday[0] ?>" />
              <input type="submit" name="link" value="予定を登録" />
            </form>
          </td>

          <td id="schedule2">
            <form action="Schedule.php" method="POST">
              <input type="hidden" name="date" value="<?= $searchday[1] ?>" />
              <input type="submit" name="link" value="予定を登録" />
            </form>
          </td>

          <td id="schedule3">
            <form action="Schedule.php" method="POST">
              <input type="hidden" name="date" value="<?= $searchday[2] ?>" />
              <input type="submit" name="link" value="予定を登録" />
            </form>
          </td>

          <td id="schedule4">
            <form action="Schedule.php" method="POST">
              <input type="hidden" name="date" value="<?= $searchday[3] ?>" />
              <input type="submit" name="link" value="予定を登録" />
            </form>
          </td>

          <td id="schedule5">
            <form action="Schedule.php" method="POST">
              <input type="hidden" name="date" value="<?= $searchday[4] ?>" />
              <input type="submit" name="link" value="予定を登録" />
            </form>
          </td>

          <td id="schedule6">
            <form action="Schedule.php" method="POST">
              <input type="hidden" name="date" value="<?= $searchday[5] ?>" />
              <input type="submit" name="link" value="予定を登録" />
            </form>
          </td>

          <td id="schedule7">
            <form action="Schedule.php" method="POST">
              <input type="hidden" name="date" value="<?= $searchday[6] ?>" />
              <input type="submit" name="link" value="予定を登録" />
            </form>
          </td>

        </tr>
        <?php
        for($i=0; $i<$membername_count; $i++) {
          echo $form[$i];
        }
        ?>
      </table>
    </div>
  </div>
</div>
</body>
</html>

<?php
$sql = "SELECT * FROM " .$user_name. " WHERE startday_figure<=? AND ?<=stopday_figure";
for($i=0; $i<7; $i++) {
  try {
    $add_id = "#schedule" . ($i+1);
    $pdo_schedule = new PDO($dsn_schedule, $db_schedule['user'], $db_schedule['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    $stmt = $pdo_schedule->prepare($sql);
    $stmt->execute(array($day_figure[$i],$day_figure[$i]));
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $row = escape($row);
      $temp_schedule = $row["schedule"];
      $temp_startday_figure = $row["startday_figure"];
      $temp_schedule_id = $row["id"];
      $check_schedule = "schedule_".$temp_startday_figure."_".$temp_schedule_id."_".$user_id."_".$i;
      $tab_id = $row["tab_id"];
      $repeat_terms = $row["repeat_terms"];
      if($tab_id==1 || $tab_id==2 || ($tab_id==3 && $repeat_terms==1)) {
        print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
      } else if($tab_id == 3) {
        if($repeat_terms == 2) {
          if($week_array[$i]!=0 && $week_array[$i]!=6)
          print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
        } else if($repeat_terms == 3) {
          if(isset($row["terms_week"]) && isset($row["terms_weekname"])) {
            $terms_week = $row["terms_week"];
            $terms_weekname = $row["terms_weekname"];
            if($terms_week==1 && $week_name[$week_array[$i]]==$terms_weekname) {
              print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
            } else if($terms_week==2 && $week_name[$week_array[$i]]==$terms_weekname && (1<=intval($day_array[$i]) && intval($day_array[$i])<=7)) {
              print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
            } else if($terms_week==3 && $week_name[$week_array[$i]]==$terms_weekname && (8<=intval($day_array[$i]) && intval($day_array[$i])<=14)) {
              print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
            } else if($terms_week==4 && $week_name[$week_array[$i]]==$terms_weekname && (15<=intval($day_array[$i]) && intval($day_array[$i])<=21)) {
              print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
            } else if($terms_week==5 && $week_name[$week_array[$i]]==$terms_weekname && (22<=intval($day_array[$i]) && intval($day_array[$i])<=29)) {
              print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
            } else if($terms_week==6) {
              if($terms_weekname == "日") $lastday = "last Sunday";
              else if($terms_weekname == "月") $lastday = "last Monday";
              else if($terms_weekname == "火") $lastday = "last Tuesday";
              else if($terms_weekname == "水") $lastday = "last Wednesday";
              else if($terms_weekname == "木") $lastday = "last Thursday";
              else if($terms_weekname == "金") $lastday = "last Friday";
              else if($terms_weekname == "土") $lastday = "last Saturday";
              $nextmonth = date("Y-m-01", strtotime('+1 month'));
              $lastday =  date("d", strtotime($lastday, strtotime($nextmonth)));
              if($lastday == $day_array[$i]) {
                print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
              }
            }
          } else {
            echo "schedule error<br>";
            exit;
          }
        } else if($repeat_terms == 4) {
          if(isset($row["terms_day"])) {
            $terms_day = $row["terms_day"];
            if($day_array[$i] == $terms_day)
            print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\")</script>";
          } else {
            echo "terms_day error";
            exit;
          }
        }
      }
    }
  } catch(PDOException $e) {
    header('Content-Type: text/plain; charset=UTF-8', true, 500);
    exit($e->getMessage());
  }
}
?>

<?php
$count = 0;
foreach ($member_name as $value) {
  $username = htmlspecialchars($value, ENT_QUOTES);
  $sql = "SELECT * FROM " .$username. " WHERE startday_figure<=? AND ?<=stopday_figure";
  for($i=0; $i<7; $i++) {
    try {
      $add_id = "#schedule" .($i+1)."_".$value;
      $pdo_schedule = new PDO($dsn_schedule, $db_schedule['user'], $db_schedule['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
      $stmt = $pdo_schedule->prepare($sql);
      $stmt->execute(array($day_figure[$i],$day_figure[$i]));
      while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row = escape($row);
        $temp_schedule = $row["schedule"];
        $temp_startday_figure = $row["startday_figure"];
        $temp_schedule_id = $row["id"];
        $check_schedule = "schedule_".$temp_startday_figure."_".$temp_schedule_id."_".$member_id[$value]."_".$i;
        $tab_id = $row["tab_id"];
        $repeat_terms = $row["repeat_terms"];
        if($tab_id==1 || $tab_id==2 || ($tab_id==3 && $repeat_terms==1)) {
          print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
        } else if($tab_id == 3) {
          if($repeat_terms == 2) {
            if($week_array[$i]!=0 && $week_array[$i]!=6)
            print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
          } else if($repeat_terms == 3) {
            if(isset($row["terms_week"]) && isset($row["terms_weekname"])) {
              $terms_week = $row["terms_week"];
              $terms_weekname = $row["terms_weekname"];
              if($terms_week==1 && $week_name[$week_array[$i]]==$terms_weekname) {
                print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
              } else if($terms_week==2 && $week_name[$week_array[$i]]==$terms_weekname && (1<=intval($day_array[$i]) && intval($day_array[$i])<=7)) {
                print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
              } else if($terms_week==3 && $week_name[$week_array[$i]]==$terms_weekname && (8<=intval($day_array[$i]) && intval($day_array[$i])<=14)) {
                print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
              } else if($terms_week==4 && $week_name[$week_array[$i]]==$terms_weekname && (15<=intval($day_array[$i]) && intval($day_array[$i])<=21)) {
                print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
              } else if($terms_week==5 && $week_name[$week_array[$i]]==$terms_weekname && (22<=intval($day_array[$i]) && intval($day_array[$i])<=29)) {
                print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
              } else if($terms_week==6) {
                if($terms_weekname == "日") $lastday = "last Sunday";
                else if($terms_weekname == "月") $lastday = "last Monday";
                else if($terms_weekname == "火") $lastday = "last Tuesday";
                else if($terms_weekname == "水") $lastday = "last Wednesday";
                else if($terms_weekname == "木") $lastday = "last Thursday";
                else if($terms_weekname == "金") $lastday = "last Friday";
                else if($terms_weekname == "土") $lastday = "last Saturday";
                $nextmonth = date("Y-m-01", strtotime('+1 month'));
                $lastday =  date("d", strtotime($lastday, strtotime($nextmonth)));
                if($lastday == $day_array[$i]) {
                  print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
                }
              }
            } else {
              echo "schedule error<br>";;
              exit;
            }
          } else if($repeat_terms == 4) {
            if(isset($row["terms_day"])) {
              $terms_day = $row["terms_day"];
              if($day_array[$i] == $terms_day)
              print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\",\"".$searchday[$i]."\")</script>";
            } else {
              echo "terms_day error";
              exit;
            }
          }
        }
      }
    } catch(PDOException $e) {
      header('Content-Type: text/plain; charset=UTF-8', true, 500);
      exit($e->getMessage());
    }
  }
}
?>
