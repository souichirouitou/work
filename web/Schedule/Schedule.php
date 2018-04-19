<?php
// ログイン状態チェック
session_start();
if (!isset($_SESSION["NAME"])) {
  header("Location: Logout.php");
  exit();
} else {
  require_once ('escape.php');
  require_once ('database_info.php');
  $dsn_login = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db_login['host'], $db_login['dbname']);
  if(isset($_POST["addCount"])) $addCount = $_POST["addCount"];
  else $addCount = 0;
  if(isset($_POST["redCount"])) $redCount = $_POST["redCount"];
  else $redCount = 0;
}
?>

 <!DOCTYPE html>
 <html>
 <head>
 <meta charset="utf-8">
  <title>予定登録ページ</title>

  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
  <script src="js/function.js" language="JavaScript" type="text/javascript"></script>
  <link rel="stylesheet" type="text/css" href="css/menu.css">
  <link rel="stylesheet" type="text/css" href="css/tabmenu.css">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <link rel="stylesheet" type="text/css" href="css/panmenu.css">
  <link rel="stylesheet" type="text/css" href="css/schedule.css">
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
    document.scheduleForm1.submit();
  }
  function send2() {
    participateData.forEach(function(userData){
      count++;
      $('.userInfo').append("<input type=\"hidden\" name=\"username[]\" value=\"" + userData + "\" />");
    });
    document.scheduleForm2.submit();
  }
  function send3() {
    participateData.forEach(function(userData){
      count++;
      $('.userInfo').append("<input type=\"hidden\" name=\"username[]\" value=\"" + userData + "\" />");
    });
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
        <!--<li><a href="#menu1">登録情報変更</a>-->
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

<!-- パンくずメニュー(仮) -->
<div class="pan_menu">
  <div class="pan1">
    マイページ -> 予定の登録
  </div>
</div>

<!-- タブメニュー実装 -->
<div class="tabbox">
  <p class="tabs">
    <a href="#tab1" class="tab1" onclick="ChangeTab('tab1'); return false;">通常予定</a>
    <a href="#tab2" class="tab2" onclick="ChangeTab('tab2'); return false;">連日予定</a>
    <a href="#tab3" class="tab3" onclick="ChangeTab('tab3'); return false;">繰り返し予定</a>
  </p>
  <div id="tab1" class="tab">
    <div class="schedule__day">
      <form name="scheduleForm1" id="scheduleForm1" action="addSchedule.php" method="post">
        <input type="hidden" name="tab_id" value="1">
        <dl>
          <dt>日付</dt>
          <dd><input type="date" class="startday" name="startday" value="<?php echo $_POST["date"]; ?>" placeholder="" /></dd>
          <dt>時間</dt>
          <dd>開始 : <input type="time" class="starttime" name="starttime" />　　〜　　終了 : <input type="Time" class="stoptime" name="stoptime" /></dd>
          <dt>予定</dt>
          <dd><input type="text" class="schedule" name="schedule" placeholder="予定タイトル入力"/></dd>
          <dt>メモ（連絡事項）</dt>
          <dd><textarea name="memo" class="memo" placeholder="400文字上限" maxlength="400"></textarea></dd>
          <dt>参加者</dt>
          <dd>
          <div class="user__Box">
          <div class="userSelect__Left">
          <?php
          try {
            $pdo_login = new PDO($dsn_login, $db_login['user'], $db_login['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
            $query = sprintf("select name from userData");
            $stmt = $pdo_login->query($query);
            print "<select name=\"user[]\" class=\"user\" multiple>";
            while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
              print "<option value=\"" .$result['name']. "\">" .htmlspecialchars($result['name'], ENT_QUOTES, false). "</option>";
            }
            print "</select>";
          } catch(PDOException $e) {
            header('Content-Type: text/plain; charset=UTF-8', true, 500);
            exit($e->getMessage());
          }
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
      <form name="scheduleForm2" id="scheduleForm2" action="addSchedule.php" method="post">
        <input type="hidden" name="tab_id" value="2">
        <dl>
          <dt>日付</dt>
          <dd><input type="date" class="startday" name="startday" value="<?php echo $_POST["date"]; ?>" placeholder="" />　　〜　　
          <input type="date" class="stopday" name="stopday" value="<?php echo $_POST["date"]; ?>" placeholder="" /></dd>
          <dt>時間</dt>
          <dd>開始 : <input type="time" class="starttime" name="starttime" />　　〜　　終了 : <input type="Time" class="stoptime" name="stoptime" /></dd>
          <dt>予定</dt>
          <dd><input type="text" class="schedule" name="schedule" placeholder="予定タイトル入力"/></dd>
          <dt>メモ（連絡事項）</dt>
          <dd><textarea name="memo" class="memo" placeholder="400文字上限" maxlength="400"></textarea></dd>
          <dt>参加者</dt>
          <dd>
          <div class="user__Box">
          <div class="userSelect__Left">
          <?php
          try {
            $pdo_login = new PDO($dsn_login, $db_login['user'], $db_login['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
            $query = sprintf("select name from userData");
            $stmt = $pdo_login->query($query);
            print "<select name=\"user[]\" class=\"user\" multiple>";
            while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
              print "<option value=\"" .$result['name']. "\">" .htmlspecialchars($result['name'], ENT_QUOTES, false). "</option>";
            }
            print "</select>";
          } catch(PDOException $e) {
            header('Content-Type: text/plain; charset=UTF-8', true, 500);
            exit($e->getMessage());
          }
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
        <input type="button" name="action" class="action" value="送信" onClick="send2()">
      </form>
    </div>
  </div>

  <!-- 繰り返し予定の追加 -->
  <div id="tab3" class="tab">
    <div class="schedule__day">
      <form name="scheduleForm3" id="scheduleForm3" action="addSchedule.php" method="post">
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
            <input type="date" class="startday" name="startday" value="<?php echo $_POST["date"]; ?>" placeholder="" />　　〜　　
            <input type="date" class="stopday" name="stopday" value="<?php echo $_POST["date"]; ?>" placeholder="" />
          </dd>
          <dt>時間</dt>
          <dd>開始 : <input type="time" class="starttime" name="starttime" />　　〜　　終了 : <input type="Time" class="stoptime" name="stoptime" /></dd>
          <dt>予定</dt>
          <dd><input type="text" class="schedule" name="schedule" placeholder="予定タイトル入力" /></dd>
          <dt>メモ（連絡事項）</dt>
          <dd><textarea name="memo" class="memo" placeholder="400文字上限" maxlength="400" /></textarea></dd>
          <dt>参加者</dt>
          <dd>
          <div class="user__Box">
          <div class="userSelect__Left">
          <?php
          try {
            $pdo_login = new PDO($dsn_login, $db_login['user'], $db_login['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
            $query = sprintf("select name from userData");
            $stmt = $pdo_login->query($query);
            print "<select name=\"user[]\" class=\"user\" multiple>";
            while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
              print "<option value=\"" .$result['name']. "\">" .htmlspecialchars($result['name'], ENT_QUOTES, false). "</option>";
            }
            print "</select>";
          } catch(PDOException $e) {
            header('Content-Type: text/plain; charset=UTF-8', true, 500);
            exit($e->getMessage());
          }
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
        <input type="button" name="action" class="action" value="送信" onClick="send3()">
      </form>
    </div>
  </div>
</div><!-- tabbox -->

<script type="text/javascript">
   ChangeTab('tab1');
</script>
</body>
</html>
