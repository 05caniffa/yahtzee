<?php
/*
 Copyright (c) 2010 Andrew Caniff <andrew.caniff@gmail.com>

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.

*/

require_once("common.php");

class GameVars{
  public $score_items=array();
  function __construct(){

    $addup=function($self,$array){
      $score=0;
      foreach($array as $item){
	if($self->val==$item)
	  $score+=$item;
      }
      return $score;
    };

    $addall=function($self,$array){
      return array_sum($array);
    };
    $static=function($self,$array){
      return $self->static_score;
    };

    $verify_true=function($self,$array){
      return true;
    };

    $check_same=function($self,$array){
      $counts=array(0,0,0,0,0,0);
      foreach($array as $item){
	$counts[$item-1]++;
      }
      return max($counts)>=$self->val;
    };

    $check_straight=function($self,$array){
      $array=array_values(array_unique($array));
      sort($array);
      $chain=0;
      $max_chain=0;
      for($i=1;$i<count($array);$i++){
	if($array[$i-1]+1==$array[$i]){
	  $chain=$chain==0?2:$chain+1;
	}
	else
	  $chain=0;
	$max_chain=max($max_chain,$chain);
      }
      return $max_chain>=$self->val;
    };

    $check_fh=function($self,$array){
      sort($array);
      $n=$array[0];
      $m=$array[4];
      $counts=array(0,0,0,0,0,0);
      foreach($array as $item){
	$counts[$item-1]++;
      }
      $fh_ref=array(2,3);
      $actual=array($counts[$n-1],$counts[$m-1]);
      sort($actual);
      return ($fh_ref===$actual);
    };


    $this->score_items["ones"]=new ScoreItem($addup,$verify_true,"ones",0,"Ones",1);
    $this->score_items["twos"]=new ScoreItem($addup,$verify_true,"twos",1,"Twos",2);
    $this->score_items["threes"]=new ScoreItem($addup,$verify_true,"threes",2,"Threes",3);
    $this->score_items["fours"]=new ScoreItem($addup,$verify_true,"fours",3,"Fours",4);
    $this->score_items["fives"]=new ScoreItem($addup,$verify_true,"fives",4,"Fives",5);
    $this->score_items["sixes"]=new ScoreItem($addup,$verify_true,"sixes",5,"Sixes",6);
    $this->score_items["threekind"]=new ScoreItem($addall,$check_same,"threekind",6,"Three of a Kind",3,"bottom");
    $this->score_items["fourkind"]=new ScoreItem($addall,$check_same,"fourkind",7,"Four of a Kind",4,"bottom");
    $this->score_items["fullhouse"]=new ScoreItem($static,$check_fh,"fullhouse",8,"Full House",0,"bottom",25);
    $this->score_items["smstraight"]=new ScoreItem($static,$check_straight,"smstraight",9,"Small Straight",4,"bottom",30);
    $this->score_items["lgstraight"]=new ScoreItem($static,$check_straight,"lgstraight",10,"Large Straight",5,"bottom",40);
    $this->score_items["chance"]=new ScoreItem($addall,$verify_true,"chance",11,"Chance",1,"bottom");
    $this->score_items["yahtzee"]=new ScoreItem($static,$check_same,"yahtzee",12,"Yahtzee",5,"bottom",50);
    $this->score_items["yahtzeebonus"]=new ScoreItem($static,$check_same,"yahtzeebonus",13,"Yahtzee Bonus",5,"bottom",100);
  }
  function get_score_items(){
    return $this->score_items;
  }
}

class ScoreItem{
  public $id;
  public $text;
  public $val;
  public $section;
  public $score_function;
  public $verify_function;
  public $static_score;
  function __construct($score_function,$verify_function,$id,$index,$text,$val,$section="top",$static_score=NULL){
    $this->id=$id;
    $this->text=$text;
    $this->val=$val;
    $this->section=$section;
    $this->score_function=$score_function;
    $this->verify_function=$verify_function;
    $this->static_score=$static_score;
  }
  function get_score($array){
    $self=$this;
    if($this->verify($array))
      return call_user_func($this->score_function,$self,$array);
    else
      return 0;
  }
  function verify($array){
    $self=$this;
    return call_user_func($this->verify_function,$self,$array);
  }
}

function score_sheet(){
  global $gv,$first_check;
  $first_check=true;
  $top=array();
  $bottom=array();
  foreach($gv->get_score_items() as $key=>$obj){
    if($obj->section=="top")
      array_push($top,$obj);
    else
      array_push($bottom,$obj);
  }
  printf("<table class=\"scoresheet\">\n");
  $top_subtotal=score_section($top);
  printf("<tr><td><b>Top subtotal</b></td><td>%d</td></tr>",$top_subtotal);
  $bonus=$top_subtotal>=63?35:0;
  printf("<tr><td>If subtotal is 63 or higher, add 35</td><td>%d</td></tr>",$bonus);
  $top_total=$top_subtotal+$bonus;
  printf("<tr><td><b>Top total</b></td><td>%d</td></tr>",$top_total);
  $bottom_total=score_section($bottom);
  printf("<tr><td><b>Bottom total</b></td><td>%d</td></tr>",$bottom_total);
  $total=$top_total+$bottom_total;
  printf("<tr><td><b>Total</b></td><td>%d</td></tr>",$total);
  printf("</table>\n");
}

function score_section($list){
  global $gv,$dice,$first_check;
  $total=0;
  foreach($list as $obj){
    $checked="";
    $enabled=($_SESSION['turn_num']==3&&(!is_numeric($_SESSION[$obj->id]))?"":"disabled");
    if($obj->id=="yahtzeebonus"){
      $enabled="disabled";
    }
    if($first_check&&$enabled==""){
      $checked="checked";
      $first_check=false;
    }
    if($obj->id=="yahtzee"){
      if(is_numeric($_SESSION[$obj->id])){
	$_SESSION['has_yahtzee']=true;
      }
    }
    printf("<tr><td>%s</td><td>%s</td><td><input type=\"radio\" name=\"selection\" value=\"%s\" %s %s></td></tr>",$obj->text,$_SESSION[$obj->id],$obj->id,$checked,$enabled);
    if(is_numeric($_SESSION[$obj->id])){
      $total+=$_SESSION[$obj->id];
    }
  }
  return $total;
}

function show_dice(){
  global $dice;
  printf("<table>\n<tr>");
  for($i=0;$i<5;$i++){
    printf("<td>%s<input type=\"hidden\" name=\"%s_val\" value=\"%s\"></td>",$dice[$i],"d".$i,$dice[$i]);
  }
  printf("</tr>\n<tr>");
  for($i=0;$i<5;$i++){
    printf("<td><input type=\"checkbox\" name=\"%s_check\" value=\"checked\" %s></td>","d".$i,$_POST["d".$i.'_check']);
  }
  printf("</tr>\n</table>");
}

function roll(){
  global $dice;
  for($i=0;$i<5;$i++){
    if($_POST["d".$i."_check"]=="checked"){
      $dice[$i]=$_POST["d".$i."_val"];
    }
    else{
      //(re)roll that die
      $dice[$i]=rand(1,6);
      $dice[$i]=5;
    }
  }
}

function play_surface(){
  $is_turn=true;
  printf("<form action=\"%s\" method=\"POST\">\n",$_SERVER['PHP_SELF']);
  score_sheet();
  if($_SESSION['rounds']<13){
    roll_form();
  }
  else{
    printf("<input type=\"submit\" value=\"New Game?\">\n");
    printf("<input type=\"hidden\" name=\"_newgame\" value=\"true\">\n");
  }
  printf("</form>\n");
}

function roll_form(){
  global $dice,$gv;
  if($_SESSION['turn_num']>0){
    if($_SESSION['turn_num']<=3)
      roll();
    show_dice();
  }
  if($_SESSION['turn_num']<3){
    printf("<input type=\"submit\" value=\"Roll\">\n");
    printf("<input type=\"hidden\" name=\"_roll\" value=\"true\">\n");
  }
  else{
    if($_SESSION['has_yahtzee']){
      $si_arr=$gv->get_score_items();
      $si=$si_arr['yahtzee'];
      if($si->verify($dice)){
	printf("Yahtzee bonus! 100 points will automatically be added to the Yahtzee Bonus entry<br>");
	$_SESSION['_bonus']=true;
      }
    }
    printf("<input type=\"submit\" value=\"Make Selection\">\n");
    printf("<input type=\"hidden\" name=\"_select\" value=\"true\">\n");
  }
}

function full_page(){
  start_page("Game");
  controls();
  play_surface();
  logout();
  close_page();
}

function calc_score($selection){
  global $gv,$dice;
  $si_arr=$gv->get_score_items();
  $si=$si_arr[$selection];
  $old_score=0;
  if($selection=="yahtzeebonus"&&is_numeric($_SESSION['yahtzeebonus']))
    $old_score=$_SESSION['yahtzeebonus'];
  $_SESSION[$selection]=$si->get_score($dice)+$old_score;
}

session_start();

if(!isset($_SESSION['key'])){
  header("Location: index.php");
}
else{
  $gv=new GameVars();
  $dice=array(0,0,0,0,0);
  if($_SERVER['REQUEST_METHOD']=="POST"){
    if(array_key_exists('_logout',$_POST)){
      session_destroy();
      header("Location: index.php");
    }
    else{
      for($i=0;$i<5;$i++){
	$dice[$i]=$_POST["d".$i."_val"];
      }
      if(array_key_exists('_roll',$_POST)){
	$_SESSION['_valid']=true;
	$_SESSION['turn_num']=$_SESSION['turn_num']+1;
	printf("turn number = %d",$_SESSION['turn_num']);
      }
      else if(array_key_exists('_select',$_POST)){ //make selection
	if($_SESSION['_valid']){
	  $_SESSION['turn_num']=0;
	  calc_score($_POST['selection']);
	  if($_SESSION['_bonus']){
	    calc_score('yahtzeebonus');
	    $_SESSION['bonus']=false;
	  }
	  $_SESSION["_valid"]=false;
	  $_SESSION['rounds']=array_key_exists('rounds',$_SESSION)?$_SESSION['rounds']+1:1;
	  printf("rounds completed: %d",$_SESSION['rounds']);
	}
      }
      else{//new game
	$key=$_SESSION['key'];
	foreach(array_keys($_SESSION) as $key){
	  unset($_SESSION[$key]);
	}
	$_SESSION['key']=$key;
      }
      full_page();
    }
  }
  else{
    full_page();
  }
}