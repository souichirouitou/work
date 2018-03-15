<?php
session_start();
//htmlspecialchars($_SESSION["NAME"], ENT_QUOTES);
// ログイン状態チェック
if (!isset($_SESSION["NAME"])) {
  header("Location: Logout.php");
  exit;
} else {
  $db['host'] = "localhost"; //DBサーバのURL
  $db['user'] = "schedule"; // ユーザ名
  $db['pass'] = "Schedule12()?A"; // 上記ユーザのパスワード
  $db['dbname'] = "scheduleManagement"; // データベース名
  $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
  $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
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
//$show_week = $next_week + $prev_week;
$show_week = $next_week;
$week_name = array("日", "月", "火", "水", "木", "金", "土"); /* $week_name[$w] */
$mFlag = -1; // 現在の月が何日まであるか
$lFlag = -1; // うるう年かどうか
$searchday = array(); // スケジュールを表示する日
$week_array = array(); // 〃曜日
$day_figure = array(); // day_figureカラムとの比較用
for($i=0; $i<7; $i++) {
  $plus_day = "+".$i." day";
  $year_array[] = date("Y", strtotime($plus_day)+$show_week);
  $month_array[] = date("m", strtotime($plus_day)+$show_week);
  $day_array[] = date("d", strtotime($plus_day)+$show_week);;
  $week_array[] = date("w", strtotime($plus_day)+$show_week);
  $searchday[] = $year_array[$i]."-".$month_array[$i]."-".$day_array[$i];
  $day_figure[] = $year_array[$i].$month_array[$i].$day_array[$i];
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
    }
} else {
    $mFlag = 31;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
  <title>main page</title>
  <link href="css/kalendar.css" rel="stylesheet">
  <style>
  .kalendar {
    width: 600px;
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
  <!-- スケジュール処理 -->
  <script type="text/javascript">
  function view1(add_id,check_schedule,schedule_id,schedule,startday,stopday,starttime,stoptime,memo,repeat_terms,terms_week,terms_weekname,terms_day,tab_id) {
    var check = add_id+" "+check_schedule+" "+schedule_id+" "+schedule+" "+startday+" "+stopday+" "+starttime+" "+stoptime+" "+memo;
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
  <!--
  <header class='container'>
  </header>
-->

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
              <?php
              if($week_array[0]==0 || $week_array[0]==6) {
                  /* 背景色を変更する操作を入れたい */
              }
              echo ($day_array[0]). " (" .$week_name[$week_array[0]]. ")";
              ?>
            </td>
            <td id="day2" width="13%">
              <?php
              $dayCheck = $day_array[1];
              if(($week_array[1])>6) {
                $dailyCheck = ($week_array[1]) - 7;
                if($dailyCheck==0 || $dailyCheck==6) {
                }
              } else {
                $dailyCheck = $week_array[1];
                if(($dailyCheck)==0 || ($dailyCheck)==6) {
                }
              }
              if($dayCheck > $mFlag) {
                $dayCheck = $dayCheck - $mFlag;
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              } else {
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              }
              ?>
            </td>

            <td id="day3" width="13%">
              <?php
              $dayCheck = $day_array[2];
              if(($week_array[2])>6) {
                $dailyCheck = ($week_array[2]) - 7;
                if($dailyCheck==0 || $dailyCheck==6) {
                  ;
                }
              } else {
                $dailyCheck = $week_array[2];
                if($dailyCheck==0 || $dailyCheck==6) {
                  ;
                }
              }
              if($dayCheck > $mFlag) {
                $dayCheck = $dayCheck - $mFlag;
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              } else {
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              }
              ?>
            </td>

            <td id="day4" width="13%">
              <?php
              $dayCheck = $day_array[3];
              if(($week_array[3])>6) {
                $dailyCheck = ($w+3) - 7;
                if($dailyCheck==0 || $dailyCheck==6) {
                }
              } else {
                $dailyCheck = $week_array[3];
                if(($dailyCheck)==0 || ($dailyCheck)==6) {
                }
              }
              if($dayCheck > $mFlag) {
                $dayCheck = $dayCheck - $mFlag;
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              } else {
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              }
              ?>
            </td>

            <td id="day5" width="13%">
              <?php
              $dayCheck = $day_array[4];
              if(($week_array[4])>6) {
                $dailyCheck = ($week_array[4]) - 7;
                if($dailyCheck==0 || $dailyCheck==6) {
                }
              } else {
                $dailyCheck = $week_array[4];
                if(($dailyCheck)==0 || ($dailyCheck)==6) {
                }
              }
              if($dayCheck > $mFlag) {
                $dayCheck = $dayCheck - $mFlag;
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              } else {
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              }
              ?>
            </td>

            <td id="day6" width="13%">
              <?php
              $dayCheck = $day_array[5];
              if(($week_array[5])>6) {
                $dailyCheck = ($week_array[5]) - 7;
                if($dailyCheck==0 || $dailyCheck==6) {
                }
              } else {
                $dailyCheck = $week_array[5];
                if(($dailyCheck)==0 || ($dailyCheck)==6) {
                }
              }
              if($dayCheck > $mFlag) {
                $dayCheck = $dayCheck - $mFlag;
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              } else {
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              }
              ?>
            </td>

            <td id="day7" width="13%">
              <?php
              $dayCheck = $day_array[6];
              if(($week_array[6])>6) {
                $dailyCheck = ($week_array[6]) - 7;
                if($dailyCheck==0 || $dailyCheck==6) {
                }
              } else {
                $dailyCheck = $week_array[6];
                if(($dailyCheck)==0 || ($dailyCheck)==6) {
                }
              }
              if($dayCheck > $mFlag) {
                $dayCheck = $dayCheck - $mFlag;
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              } else {
                echo $dayCheck. " (" .$week_name[$dailyCheck]. ")";
              }
              ?>
            </td>

          </tr>
          <tr>
            <td><?php echo htmlspecialchars($_SESSION["NAME"], ENT_QUOTES); ?></td>
            <td id="schedule1">
              <!-- スケジュール取得 -->
              <!-- 予定登録 -->
              <form action="Schedule.php" method="POST">
                <input type="hidden" name="date" value="<?php echo $searchday[0]; ?>" />
                <input type="submit" name="link" value="予定を登録" />
              </form>
            </td>

            <td id="schedule2">
              <!-- スケジュール取得 -->
              <!-- 予定登録 -->
              <form action="Schedule.php" method="POST">
                <input type="hidden" name="date" value="<?php echo $searchday[1]; ?>" />
                <input type="submit" name="link" value="予定を登録" />
              </form>
            </td>

            <td id="schedule3">
              <!-- スケジュール取得 -->
              <!-- 予定登録 -->
              <form action="Schedule.php" method="POST">
                <input type="hidden" name="date" value="<?php echo $searchday[2]; ?>" />
                <input type="submit" name="link" value="予定を登録" />
              </form>
            </td>

            <td id="schedule4">
              <!-- スケジュール取得 -->
              <!-- 予定登録 -->
              <form action="Schedule.php" method="POST">
                <input type="hidden" name="date" value="<?php echo $searchday[3]; ?>" />
                <input type="submit" name="link" value="予定を登録" />
              </form>
            </td>

            <td id="schedule5">
              <!-- スケジュール取得 -->
              <!-- 予定登録 -->
              <form action="Schedule.php" method="POST">
                <input type="hidden" name="date" value="<?php echo $searchday[4]; ?>" />
                <input type="submit" name="link" value="予定を登録" />
              </form>
            </td>

            <td id="schedule6">
              <!-- スケジュール取得 -->
              <!-- 予定登録 -->
              <form action="Schedule.php" method="POST">
                <input type="hidden" name="date" value="<?php echo $searchday[5]; ?>" />
                <input type="submit" name="link" value="予定を登録" />
              </form>
            </td>

            <td id="schedule7">
              <!-- スケジュール取得 -->
              <!-- 予定登録 -->
              <form action="Schedule.php" method="POST">
                <input type="hidden" name="date" value="<?php echo $searchday[6]; ?>" />
                <input type="submit" name="link" value="予定を登録" />
              </form>
            </td>
          </tr>
        </table>
      </div>
    </div>
</div>

<div class="box__kalendar"></div>

<script src="//code.jquery.com/jquery-2.0.3.min.js"></script>
<script src="js/kalendar.js"></script>
<script>
$(document).ready(function() {
  $('.box__kalendar').kalendar({
    events: [
      {
        title:"タイトル",
        start: {
          date: 20000000,
          time: "12.00"
        },
        end: {
          date: "20000000",
          time: "14.00"
        },
        location: "Japan",
        color: "yellow"
      },
    ],
    eventcolors: {
      yellow: {
        background: "#FC0",
        text: "#000",
        link: "#000"
      }
    },
    color: "Blue",
    firstDayOfWeek: "Sunday"
  });
});
</script>
</body>
</html>

<?php
$username = htmlspecialchars($_SESSION["NAME"], ENT_QUOTES);
$sql = "SELECT * FROM " .$username. " WHERE startday_figure<=? AND ?<=stopday_figure";
for($i=0; $i<7; $i++) {
  $add_id = "#schedule" . ($i+1);
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array($day_figure[$i],$day_figure[$i]));
  while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $temp_schedule = $row["schedule"];
    $temp_startday_figure = $row["startday_figure"];
    $temp_schedule_id = $row["id"];
    $check_schedule = "schedule_".$temp_startday_figure."_".$temp_schedule_id."_".$i;
    $tab_id = $row["tab_id"];
    $repeat_terms = $row["repeat_terms"];
    if($tab_id==1 || $tab_id==2 || ($tab_id==3 && $repeat_terms==1)) {
      print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\")</script>";
    } else if($tab_id == 3) {
      if($repeat_terms == 2) {
        if($week_array[$i]!=0 && $week_array[$i]!=6)
        print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\")</script>";
      } else if($repeat_terms == 3) {
        if(isset($row["terms_week"]) && isset($row["terms_weekname"])) {
          $terms_week = $row["terms_week"];
          $terms_weekname = $row["terms_weekname"];
          if($terms_week==1 && $week_name[$week_array[$i]]==$terms_weekname) {
            print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\")</script>";
          } else if($terms_week==2 && $week_name[$week_array[$i]]==$terms_weekname && (1<=intval($day_array[$i]) && intval($day_array[$i])<=7)) {
            print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\")</script>";
          } else if($terms_week==3 && $week_name[$week_array[$i]]==$terms_weekname && (8<=intval($day_array[$i]) && intval($day_array[$i])<=14)) {
            print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\")</script>";
          } else if($terms_week==4 && $week_name[$week_array[$i]]==$terms_weekname && (15<=intval($day_array[$i]) && intval($day_array[$i])<=21)) {
            print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\")</script>";
          } else if($terms_week==5 && $week_name[$week_array[$i]]==$terms_weekname && (22<=intval($day_array[$i]) && intval($day_array[$i])<=29)) {
            print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\")</script>";
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
              print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\")</script>";
            }
          }
        } else {
          echo "schedule error<br>";
        }
      } else if($repeat_terms == 4) {
        if(isset($row["terms_day"])) {
          $terms_day = $row["terms_day"];
          if($day_array[$i] == $terms_day)
          print "<script type=\"text/javascript\">view1(\"".$add_id."\",\"".$check_schedule."\",\"".$row["id"]."\",\"".$row["schedule"]."\",\"".$row["startday"]."\",\"".$row["stopday"]."\",\"".$row["starttime"]."\",\"".$row["stoptime"]."\",\"".$row["memo"]."\",\"".$row["repeat_terms"]."\",\"".$row["terms_week"]."\",\"".$row["terms_weekname"]."\",\"".$row["terms_day"]."\",\"".$row["tab_id"]."\")</script>";
        } else {
          echo "terms_day error";
        }
      }
    }
  }
}
?>
