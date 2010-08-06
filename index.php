<?php
require_once("common.php");

function login_form($error){
  start_page("Login");
  printf("<form method=\"POST\" action=\"%s\">\n",$_SERVER['PHP_SELF']);
  printf("<table>\n");
  printf("<tr><td>Username:</td><td><input type=\"textbox\" name=\"user\"></td></tr>\n");
  printf("<tr><td>Password:</td><td><input type=\"password\" name=\"pass\"></td></tr>\n");
  printf("<tr><td><input type=\"submit\" value=\"Login\"</td><td id=\"error\">%s</td></tr>\n",$error);
  printf("</table>\n");
  printf("</form>\n");
  close_page();
}

session_start();
if(isset($_SESSION['key'])){
  header("Location: game.php");
}
else{
  if($_SERVER['REQUEST_METHOD']=="POST"){
    $user="t";
    $pass="t";
    if($_POST['user']==$user && $_POST['pass']==$pass){
      $_SESSION['key']="true";
      header("Location: game.php");
    }
    else{
      login_form("Invalid login");
    }
  }
  else{
    login_form();
  }
}
?>

