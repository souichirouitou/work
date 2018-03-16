<?php
function get_figure($temp) {
  $date_figure = "";
  for($i=0,$count=0; $i < strlen($temp); $i++) {
    if($temp[$i] != "-" && $count == 0) {
      $date_figure .= $temp[$i];
    } else if($temp[$i] != "-" && $count == 1) {
      $date_figure .= $temp[$i];
    } else if($temp[$i] != "-" && $count == 2) {
      $date_figure .= $temp[$i];
    } else if($temp[$i] == "-") {
      $count++;
    }
  }
  return intval($date_figure);
}

/* 通常・連日予定処理 */
function addSchedule($column,$dsn,$db,$name_count) {
  if($column["tab_id"] == 1 || $column["tab_id"] == 2) {
    try {
      $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
      $query = "SELECT MAX(id) as max from allSchedule";
      $stmt = $pdo->query($query);
      if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if(isset($row["max"])) {
          $max = $row["max"];
        } else {
          $max = 0;
        }
      } else {
        echo "データベースエラー";
      }
      for($i=0; $i<$name_count; $i++) {
        $tablename = sprintf('%s',$column["username"][$i]);
        $query = "insert into " .$tablename. "(id,startday_figure,stopday_figure,startday,stopday,starttime,stoptime,schedule,memo,tab_id) values ('" .($max+1). "','" .$column["startday_figure"]. "','" .$column["stopday_figure"]. "','" .$column["startday"]. "','" .$column["stopday"] . "','" .$column["starttime"]. "','" .$column["stoptime"]. "','" .$column["schedule"]. "','" .$column["memo"]. "','" .$column["tab_id"]. "')";
        $pdo->query($query);

        $query = "insert into allSchedule values ('" .($max+1). "','" .$column["schedule"]. "','" .$tablename. "')";
        $pdo->query($query);
      }
      header("Location: Main.php");  // メイン画面へ遷移
    } catch (PDOException $e) {
        $errorMessage = 'データベースエラー';
    }
  }
  else if($column["tab_id"] == 3) {
    try {
      $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
      $query = "SELECT MAX(id) as max from allSchedule";
      $stmt = $pdo->query($query);
      if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if(isset($row["max"])) {
          $max = $row["max"];
        } else {
          $max = 0;
        }
      } else {
        echo "データベースエラー";
      }
      for($i=0; $i<$name_count; $i++) {
        $tablename = sprintf('%s',$column["username"][$i]);
        $query = "insert into " .$tablename. " values ('" .($max+1). "','" .$column["startday_figure"]. "','" .$column["stopday_figure"]. "','" .$column["startday"]. "','" .$column["stopday"] . "','" .$column["starttime"]. "','" .$column["stoptime"]. "','" .$column["schedule"]. "','" .$column["memo"]. "','" .$column["tab_id"]. "','" .$column["repeat_terms"]. "','" .$column["terms_week"]. "','" .$column["terms_weekname"]. "','" .$column["terms_day"]. "')";
        $pdo->query($query);
        $query = "insert into allSchedule values ('" .($max+1). "','" .$column["schedule"]. "','" .$tablename. "')";
        $pdo->query($query);
      }
      header("Location: Main.php");  // メイン画面へ遷移
    } catch (PDOException $e) {
        $errorMessage = 'データベースエラー';
    }
  }
}

require_once ('escape.php');
$_POST = escape($_POST); // エスケープ処理
$column = array();
$db_Flag = 1;
if(isset($_POST["tab_id"])){
  $column["tab_id"] = $_POST["tab_id"];
  if(isset($_POST["username"]) && isset($_POST["starttime"]) && isset($_POST["stoptime"])) {
    if(isset($_POST["schedule"]) && isset($_POST["memo"])) {
      if($column["tab_id"] == 3) {
        if(isset($_POST["repeat_terms"])) {
          $repeat_terms = $_POST["repeat_terms"];
          if($repeat_terms == "repeat_terms1") {
            $column = array_merge($column,array("repeat_terms"=>1, "terms_week"=>-1, "terms_weekname"=>"-1", "terms_day"=>-1));
          }
          else if($repeat_terms == "repeat_terms2") {
            $column = array_merge($column,array("repeat_terms"=>2, "terms_week"=>-1, "terms_weekname"=>"-1", "terms_day"=>-1));
          }
          else if($repeat_terms == "repeat_terms3") {
            if(isset($_POST["terms_week"])) {
              if(isset($_POST["terms_weekname"])) {
                $column = array_merge($column,array("repeat_terms"=>3, "terms_week"=>$_POST["terms_week"], "terms_weekname"=>$_POST["terms_weekname"], "terms_day"=>-1));
              } else {
                  print "term_weekname error";
                  $db_Flag = 0;
              }
            } else {
                print "terms_week error";
                $db_Flag = 0;
            }
          }
          else if($repeat_terms == "repeat_terms4") {
            if(isset($_POST["terms_day"])){
              $column = array_merge($column,array("repeat_terms"=>4, "terms_week"=>-1, "terms_weekname"=>"-1", "terms_day"=>$_POST["terms_day"]));
            } else {
              print "terms_day error<br>";
              $db_Flag = 0;
            }
          }
        } else {
          print "繰り返し条件がない";
          $db_Flag = 0;
        }
      }

      if(isset($_POST["startday"])) {
        $column["startday"] = $_POST["startday"];
        if(isset($_POST["stopday"])) {
          $column["stopday"] = $_POST["stopday"];
        }
        else if($column["tab_id"] == 1) {
          $column["stopday"] = $column["startday"];
        }
        else {
          echo "stopdayがない";
          $db_Flag = 0;
        }
      }
      else {
        echo "startdayがない";
        $db_Flag = 0;
      }

      $column["username"] = $_POST["username"];
      $column["starttime"] = $_POST["starttime"];
      $column["stoptime"] = $_POST["stoptime"];
      $column["schedule"] = trim($_POST["schedule"]);
      $column["memo"] = trim(htmlspecialchars(nl2br($_POST["memo"])));
      $column["memo"] = str_replace(array("\r", "\n"), '', $column["memo"]);
      $column["startday_figure"] = get_figure($column["startday"]);
      $column["stopday_figure"] = get_figure($column["stopday"]);
      $name_count = count($column["username"]);
    } else echo "色々ない";
  }
} else echo "tab_idない";

/* 登録処理 */
if($db_Flag == 1) {
  /* スケジュール管理データベース */
  $db['host'] = "***"; //DBサーバのURL
  $db['user'] = "***"; // ユーザ名
  $db['pass'] = "***"; // 上記ユーザのパスワード
  $db['dbname'] = "***"; // データベース名
  $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
  addSchedule($column,$dsn,$db,$name_count);
}
?>
