<?php
session_start();

// ログイン状態チェック
if (!isset($_SESSION["NAME"])) {
  header("Location: Logout.php");
  exit;
} else {
  $db['host'] = "localhost"; //DBサーバのURL
  $db['user'] = "login"; // ユーザ名
  $db['pass'] = "Login12()?A"; // 上記ユーザのパスワード
  $db['dbname'] = "loginManagement"; // データベース名
  $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']); // 認証
  try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    require_once ('escape.php');
  } catch (PDOException $e) {
    //header("Location: Logout.php");
    echo "database error";
    exit;
  }

  $username = $_SESSION["NAME"];
  $db['host'] = "localhost"; //DBサーバのURL
  $db['user'] = "schedule"; // ユーザ名
  $db['pass'] = "Schedule12()?A"; // 上記ユーザのパスワード
  $db['dbname'] = "scheduleManagement"; // データベース名
  $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']); // 認証
  try {
    $pdo2 = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
  } catch (PDOException $e) {
    //header("Location: Logout.php");
    echo "database error";
    exit;
  }
}
 ?>

  <?php
  if(isset($_POST["id"])) {
    $id = $_POST["id"];
    $query = "SELECT * from ".$username." where id = ?";
    $stmt = $pdo2->prepare($query);
    $stmt->execute(array($id));
    if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $startday = $row["startday"];
      $stopday = $row["stopday"];
      $starttime = $row["starttime"];
      $stoptime = $row["stoptime"];
      $schedule = $row["schedule"];
      $memo = $row["memo"];
    } else {
      echo "データベースエラー";
    }

    $query = "SELECT member from allSchedule WHERE id = ?";
    $stmt = $pdo2->prepare($query);
    $stmt->execute(array($id));
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $member_name[] = $row["member"];
    }
  } else {
    echo "schedule error";
  }
  $member_json = json_encode($member_name);
  ?>

<!doctype html>
<html>
  <head>
  <meta charset="utf-8">
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
  <script src="js/function.js" language="JavaScript" type="text/javascript"></script>
  <link rel="stylesheet" type="text/css" href="css/menu.css">
  <link rel="stylesheet" type="text/css" href="css/tabmenu.css">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <link rel="stylesheet" type="text/css" href="css/panmenu.css">
  <link rel="stylesheet" type="text/css" href="css/schedule.css">
  <script type="text/javascript">
  participateData = new Array();
  count = 0;
  function add() {
    var formData = $(".user").serializeArray();
    var flag = 1;

    formData.forEach(function(userData) {
      flag = 1;
      participateData.forEach(function(pData) {
        console.log('%s,%s',pData,userData.value);
        if(pData == userData.value) {
          flag = 0;
        }
      });
      if(flag == 1) {
        participateData.push(userData.value);
        console.log(participateData);
        $('.participate').append("<option class=" + userData.value + " value=" + userData.value + ">" + userData.value + "</option>");
      }
    });
    return false;
  }

  function remove() {
    var formData = $(".participate").serializeArray();
    var index;
    var rmId;

    formData.forEach(function(userData) {
      index = participateData.indexOf(userData.value);
      participateData.splice(index,1);
      console.log(participateData);
      rmId = "." + userData.value;
      $(rmId).remove();
    });
    return false;
  }

  function send1() {
    participateData.forEach(function(userData){
      count++;
      $('.userInfo').append("<input type=\"hidden\" name=\"username[]\" value=\"" + userData + "\" />");
    });
    //alert(count);
    //$('#userInfo').append("<input type=\"button\" name=\"count\" value=\"" + count + "\" />");
    //$('#participate').append("<option id=\"test\" value=\"test\">aaa</option>");
    //alert(1);
    document.scheduleForm1.submit();
  }
  function send2() {
    participateData.forEach(function(userData){
      count++;
      $('.userInfo').append("<input type=\"hidden\" name=\"username[]\" value=\"" + userData + "\" />");
    });
    //alert(count);
    //$('#userInfo').append("<input type=\"button\" name=\"count\" value=\"" + count + "\" />");
    //$('#participate').append("<option id=\"test\" value=\"test\">aaa</option>");
    //alert(1);
    document.scheduleForm2.submit();
  }
  function send3() {
    participateData.forEach(function(userData){
      count++;
      $('.userInfo').append("<input type=\"hidden\" name=\"username[]\" value=\"" + userData + "\" />");
    });
    //alert(count);
    //$('#userInfo').append("<input type=\"button\" name=\"count\" value=\"" + count + "\" />");
    //$('#participate').append("<option id=\"test\" value=\"test\">aaa</option>");
    //alert(1);
    document.scheduleForm3.submit();
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
    マイページ -> 予定確認 -> 予定の変更
  </div>
</div>

<!-- タブメニュー実装 HTML文 -->
<div class="tabbox">
  <p class="tabs">
    <a href="#tab1" class="tab1" onclick="ChangeTab('tab1'); return false;">通常予定</a>
    <a href="#tab2" class="tab2" onclick="ChangeTab('tab2'); return false;">連日予定</a>
    <a href="#tab3" class="tab3" onclick="ChangeTab('tab3'); return false;">繰り返し予定</a>
  </p>
  <div id="tab1" class="tab">
    <div class="schedule__day">
      <form name="scheduleForm1" id="scheduleForm1" action="ChangeProcess.php" method="post">
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <input type="hidden" name="tab_id" value="1">
            <dl>
              <dt>日付</dt>
              <dd><input type="date" class="startday" name="startday" value="<?php echo $startday; ?>" placeholder="" /></dd>
              <dt>時間</dt>
              <dd>開始 : <input type="time" class="starttime" name="starttime" value="<?php echo $starttime; ?>" />　　〜　　終了 : <input type="Time" class="stoptime" name="stoptime" value="<?php echo $stoptime; ?>" /></dd>
              <dt>予定</dt>
              <dd><input type="text" class="schedule" name="schedule" placeholder="予定タイトル入力" value="<?php echo $schedule; ?>"/></dd>
              <dt>メモ（連絡事項）</dt>
              <dd><textarea name="memo" class="memo" placeholder="400文字上限" maxlength="400"></textarea></dd>
              <dt>参加者</dt>
              <dd>
              <div class="user__Box">
              <div class="userSelect__Left">
              <?php
              $query = sprintf("select name from userData");
              $stmt = $pdo->query($query);
              print "<select name=\"user[]\" class=\"user\" multiple>";
              while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
                  print "<option value=\"" .$result['name']. "\">" .$result['name']. "</option>";
              }
              print "</select>";
              ?>
              </div>
              <div class="userButton">
                <input type="button" class="select_Button" onClick="add()" value="参加者の追加　→　" />
                <br><br>
                <input type="button" class="select_Button" onClick="remove()" value="　←　参加者の削除" />
              </div>
              <div class="userSelecct__Right">
                <select name="participate[]" class="participate" multiple>
                </select>
              </div>
              </div>
              </dd>
            </dl>
            <div class="userInfo"></div>
            <input type="button" name="action" class="action" value="送信" onClick="send1()">
      </form>
    </div>
  </div>

  <!-- 連日予定の追加 -->
  <div id="tab2" class="tab">
    <div class="schedule__day">
      <form name="scheduleForm2" id="scheduleForm2" action="ChangeProcess.php" method="post">
        <input type="hidden" name="tab_id" value="2">
            <dl>
              <dt>日付</dt>
              <dd><input type="date" class="startday" name="startday" value="<?php echo $startday; ?>" placeholder="" />　　〜　　
              <input type="date" class="stopday" name="stopday" value="<?php echo $stopday; ?>" placeholder="" /></dd>
              <dt>時間</dt>
              <dd>開始 : <input type="time" class="starttime" name="starttime" value="<?php echo $starttime; ?>" />　　〜　　終了 : <input type="Time" class="stoptime" name="stoptime" value="<?php echo $stoptime; ?>" /></dd>
              <dt>予定</dt>
              <dd><input type="text" class="schedule" name="schedule" placeholder="予定タイトル入力" value="<?php echo $schedule; ?>" /></dd>
              <dt>メモ（連絡事項）</dt>
              <dd><textarea name="memo" class="memo" placeholder="400文字上限" maxlength="400"></textarea></dd>
              <dt>参加者</dt>
              <dd>
              <div class="user__Box">
              <div class="userSelect__Left">
              <?php
              $query = sprintf("select name from userData");
              $stmt = $pdo->query($query);
              print "<select name=\"user[]\" class=\"user\" multiple>";
              while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
                  print "<option value=\"" .$result['name']. "\">" .$result['name']. "</option>";
              }
              print "</select>";
              ?>
              </div>
              <div class="userButton">
                <input type="button" class="select_Button" onClick="add()" value="参加者の追加　→　" />
                <br><br>
                <input type="button" class="select_Button" onClick="remove()" value="　←　参加者の削除" />
              </div>
              <div class="userSelecct__Right">
                <select name="participate[]" class="participate" multiple>
                </select>
              </div>
              </div>
              </dd>
            </dl>
            <div class="userInfo"></div>
            <input type="hidden" name="id" value="<?php echo $id; ?>" />
            <input type="button" name="action" class="action" value="送信" onClick="send2()">
      </form>
    </div>
  </div>

  <!-- 繰り返し予定の追加 -->
  <div id="tab3" class="tab">
    <div class="schedule__day">
      <form name="scheduleForm3" id="scheduleForm3" action="ChangeProcess.php" method="post">
        <input type="hidden" name="tab_id" value="3">
            <dl>
              <dt>日付</dt>
              <dd>
                繰り返し条件<br>
                <input type="radio" name="repeat_terms" value="repeat_terms1" checked="checked" />　毎日<br>
                <input type="radio" name="repeat_terms" value="repeat_terms2" />　毎日（土日除く）<br>
                <input type="radio" name="repeat_terms" value="repeat_terms3" />
                <select name="terms_week">
                  <option value="1">毎週</option>
                  <option value="2">毎月 第1</option>
                  <option value="3">毎月 第2</option>
                  <option value="4">毎月 第3</option>
                  <option value="5">毎月 第4</option>
                  <option value="6">毎月 最終</option>
                </select>
                <select name="terms_weekname">
                  <option value="日">日曜日</option>
                  <option value="月">月曜日</option>
                  <option value="火">火曜日</option>
                  <option value="水">水曜日</option>
                  <option value="木">木曜日</option>
                  <option value="金">金曜日</option>
                  <option value="土">土曜日</option>
                </select>
                <br>
                <input type="radio" name="repeat_terms" value="repeat_terms4" />　毎月
                <select name="terms_day">
                  <script type="text/javascript">
                  for(var i=1; i<=31; i++) {
                    document.write("<option value=\""+i+"\">"+i+"日</option>");
                  }
                  </script>
                </select>
                <br><br>

                <input type="date" class="startday" name="startday" value="<?php echo $startday; ?>" placeholder="" />　　〜　　
                <input type="date" class="stopday" name="stopday" value="<?php echo $stopday; ?>" placeholder="" />
              </dd>
              <dt>時間</dt>
              <dd>開始 : <input type="time" class="starttime" name="starttime" value="<?php echo $starttime; ?>" />　　〜　　終了 : <input type="Time" class="stoptime" name="stoptime" value="<?php echo $stoptime; ?>" /></dd>
              <dt>予定</dt>
              <dd><input type="text" class="schedule" name="schedule" placeholder="予定タイトル入力" value="<?php echo $schedule; ?>" /></dd>
              <dt>メモ（連絡事項）</dt>
              <dd><textarea name="memo" class="memo" placeholder="400文字上限" maxlength="400" /></textarea></dd>
              <dt>参加者</dt>
              <dd>
              <div class="user__Box">
              <div class="userSelect__Left">
              <?php
              $query = sprintf("select name from userData");
              $stmt = $pdo->query($query);
              print "<select name=\"user[]\" class=\"user\" multiple>";
              while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
                  print "<option value=\"" .$result['name']. "\">" .$result['name']. "</option>";
              }
              print "</select>";
              ?>
              </div>
              <div class="userButton">
                <input type="button" class="select_Button" onClick="add()" value="参加者の追加　→　" />
                <br><br>
                <input type="button" class="select_Button" onClick="remove()" value="　←　参加者の削除" />
              </div>
              <div class="userSelecct__Right">
                <select name="participate[]" class="participate" multiple>
                </select>
              </div>
              </div>
              </dd>
            </dl>
            <div class="userInfo"></div>
            <input type="hidden" name="id" value="<?php echo $id; ?>" />
            <input type="button" name="action" class="action" value="送信" onClick="send3()">
      </form>
    </div>
  </div>
</div><!-- tabbox -->

<!-- ページを開いた際の最初に表示されるタブの選択 -->
<script type="text/javascript">
   ChangeTab('tab1');
</script>
</body>
</html>
