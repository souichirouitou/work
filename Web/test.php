<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="description" content="kalendar のデモでーす。">
<title>kalendar - jQuery Plugin Demo</title>
<link href="css/kalendar.css" rel="stylesheet">
<style>
.kalendar {
  width: 600px;
}
</style>
</head>
<body>
<p><a href="https://webkaru.net/jquery-plugin/kalendar/">「jQueryプラグインまとめ」に戻る</a></p>
<h1>kalendar のデモ。</h1>

<div class="kalendar"></div>

<script src="//code.jquery.com/jquery-2.0.3.min.js"></script>
<script src="js/kalendar.js"></script>
<script>
$(document).ready(function() {
  $('.kalendar').kalendar({
    events: [
      {
        title:"タイトル",
        start: {
          date: 20150315,
          time: "12.00"
        },
        end: {
          date: "20150316",
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
    color: "blue",
    firstDayOfWeek: "Sunday"
  });
});
</script>
</body>
</html>
