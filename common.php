<?php

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ERROR);


function start_page($title){
  printf("<HTML>\n");
  printf("<HEAD>\n");
  printf("<TITLE>Yahtzee - %s</TITLE>\n",$title);
  printf("<STYLE TYPE=\"text/css\">");
  printf("td#error { color: #FF0000; }");
  printf("table.scoresheet {
	border-width: 1px;
	border-spacing: 2px;
	border-style: outset;
	border-color: gray;
	border-collapse: collapse;
	background-color: white;
}
table.scoresheet th {
	border-width: 1px;
	padding: 3px;
	border-style: inset;
	border-color: gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}
table.scoresheet td {
	border-width: 1px;
	padding: 3px;
	border-style: inset;
	border-color: gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}");
  printf("</STYLE>");
  printf("</HEAD>\n");
  printf("<BODY>\n");
}

function close_page(){
  printf("</BODY>\n");
  printf("</HTML>\n");
}

function logout(){
  printf("<form action=\"%s\" method=POST>\n",$_SERVER['PHP_SELF']);
  printf("<input type=\"submit\" value=\"Logout\">\n");
  printf("<input type=\"hidden\" name=\"_logout\" value=\"true\">\n");
  printf("</form>\n");
}

function controls(){
  printf("<table>\n");
  $links=array("account"=>"Account","game"=>"Current Game","new"=>"New Game");
  printf("<tr>\n");
  foreach($links as $page=>$text){
    printf("<td><a href=\"%s.php\">%s</a></td>",$page,$text);
  }
  printf("</tr>\n");
  printf("</table>\n");
}

?>