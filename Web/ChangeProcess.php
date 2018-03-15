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

function changeSchedule($column,$dsn,$db,$name_count) {
  try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    $query = "SELECT member from allSchedule WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(array($column["id"]));
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      if(isset($row["member"])) {
        $before_member[] = $row["member"];
        $rmFlag[$row["member"]] = 1;
      }
    }
  } catch (PDOException $e) {
      $errorMessage = 'データベースエラー';
  }

  foreach ((array)$column["username"] as $value2) {
    $addFlag[$value2] = 1;
    foreach ($before_member as $value1) {
        if($value1 == $value2) {
          $rmFlag[$value1] = 0;
          $addFlag[$value2] = 0;
          //echo $value1." ".$value2."<br>";
          break;
        }
      }
    }

  /* 削除 */
  try {
    foreach ((array)$before_member as $value) {
      if($rmFlag[$value] == 1) {
        $query = "DELETE FROM allSchedule WHERE member = ? AND id = ?";
        //echo $query.$value."<br>";
        $stmt = $pdo->prepare($query);
        $stmt->execute(array($value,$column["id"]));

        $query = "DELETE FROM ".$value." WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute(array($column["id"]));
      }
    }

    /* 追加 */
    foreach ((array)$column["username"] as $value) {
      if($addFlag[$value] == 1) {
        if($column["tab_id"] == 1 || $column["tab_id"] == 2) {
          $query = "insert into " .$value. "(id,startday_figure,stopday_figure,startday,stopday,starttime,stoptime,schedule,memo,tab_id) values ('" .$column["id"]. "','" .$column["startday_figure"]. "','" .$column["stopday_figure"]. "','" .$column["startday"]. "','" .$column["stopday"] . "','" .$column["starttime"]. "','" .$column["stoptime"]. "','" .$column["schedule"]. "','" .$column["memo"]. "','" .$column["tab_id"]. "')";
          $pdo->query($query);
          $query = "insert into allSchedule values ('" .$column["id"]. "','" .$column["schedule"]. "','" .$value. "')";
          $pdo->query($query);
          //header("Location: http://localhost/login_practice/Main.php");  // メイン画面へ遷移
        }
        else if($column["tab_id"] == 3) {
          $query = "insert into " .$value. " values ('" .$column["id"]. "','" .$column["startday_figure"]. "','" .$column["stopday_figure"]. "','" .$column["startday"]. "','" .$column["stopday"] . "','" .$column["starttime"]. "','" .$column["stoptime"]. "','" .$column["schedule"]. "','" .$column["memo"]. "','" .$column["tab_id"]. "','" .$column["repeat_terms"]. "','" .$column["terms_week"]. "','" .$column["terms_weekname"]. "','" .$column["terms_day"]. "')";
          $pdo->query($query);
          $query = "insert into allSchedule values ('" .$column["id"]. "','" .$column["schedule"]. "','" .$value. "')";
          $pdo->query($query);
          //header("Location: http://localhost/login_practice/Main.php");  // メイン画面へ遷移
        }
      }
    }
  }  catch (PDOException $e) {
      $errorMessage = 'データベースエラー';
  }


  /* 更新 */
  try {
    foreach ((array)$before_member as $value) {
      if($addFlag[$value]==0 && $rmFlag[$value]==0) {
        if($column["tab_id"] == 1 || $column["tab_id"] == 2) {
          $query = "UPDATE ".$value." SET startday_figure=? , stopday_figure=? , startday=? , stopday=? , starttime=? , stoptime=? , schedule=? , memo=? , tab_id=? , repeat_terms=? , terms_week=? , terms_weekname=? , terms_day=? WHERE id = ?";
          $stmt = $pdo->prepare($query);
          $stmt->execute(array($column["startday_figure"],$column["stopday_figure"],$column["startday"],$column["stopday"],$column["starttime"],$column["stoptime"],$column["schedule"],$column["memo"],$column["tab_id"],$column["repeat_terms"],$column["terms_week"],$column["terms_weekname"],$column["terms_day"],$column["id"]));
        }
        else if($column["tab_id"] == 3) {
          $query = "UPDATE ".$value." SET startday_figure=? , stopday_figure=? , startday=? , stopday=? , starttime=? , stoptime=? , schedule=? , memo=? , tab_id=? , repeat_terms=? , terms_week=? , terms_weekname=? , terms_day=? WHERE id = ?";
          $stmt = $pdo->prepare($query);
          $stmt->execute(array($column["startday_figure"],$column["stopday_figure"],$column["startday"],$column["stopday"],$column["starttime"],$column["stoptime"],$column["schedule"],$column["memo"],$column["tab_id"],$column["repeat_terms"],$column["terms_week"],$column["terms_weekname"],$column["terms_day"],$column["id"]));
        }
      }
    }
  } catch (PDOException $e) {
    $errorMessage = 'データベースエラー';
  }
  header("Location: http://localhost/login_practice/Main.php");
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
      else {
        $column = array_merge($column,array("repeat_terms"=>NULL, "terms_week"=>NULL, "terms_weekname"=>NULL, "terms_day"=>NULL));
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

      if(isset($_POST["id"])) {
        $column["id"] = $_POST["id"];
      } else {
        echo "idがない";
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
  $db['host'] = "localhost"; //DBサーバのURL
  $db['user'] = "schedule"; // ユーザ名
  $db['pass'] = "Schedule12()?A"; // 上記ユーザのパスワード
  $db['dbname'] = "scheduleManagement"; // データベース名
  $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
  changeSchedule($column,$dsn,$db,$name_count);
}
?>
