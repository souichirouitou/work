<?php
function escape($data){
  if (is_array($data)) {//データが配列の場合
    return array_map("escape",$data);
  } else {//データが配列ではない場合
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8', false);
  }
}
?>
