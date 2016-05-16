<?php
/**
* SMS.RU 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 10:04:29 [Apr 27, 2016])
*/
//
//
class sms_ru extends module {
/**
* sms_ru
*
* Module class constructor
*
* @access private
*/
function sms_ru() {
  $this->name="sms_ru";
  $this->title="SMS.RU";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();

  if ($this->data_source=='list' || $this->data_source=='') {
		if ($this->view_mode=='' || $this->view_mode=='search') {
			$this->search($out);
		}
		if ($this->view_mode=='edit') {
			$this->edit($out, $this->id);
		} 
		if ($this->view_mode=='delete') {
			$this->delete($this->id);
			$this->redirect("?");
		} 
		if ($this->view_mode=='test') {
			$this->sendNotifyAll('Тестовое сообщение от majordomo.smartliving.ru');			
			$this->redirect("?");
		} 
	}
}

/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}

function send($api_id, $phone, $message)
{	
	$ch = curl_init("http://sms.ru/sms/send");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_POSTFIELDS, array(
		"api_id"	=>	$api_id,
		"to"			=>	$phone,
		"text"		=>	$message
	));
	$body = curl_exec($ch);
	 
	$rez = explode(PHP_EOL, $body);

	if ($rez != '100')
		DebMes("SMS_RU: ".print_r($rez, true));
	 
	curl_close($ch);		 	
}

function sendNotifyAll($message)
{
   $res=SQLSelect("SELECT * FROM smsru_list WHERE ACTIVE=1");
	 
   $total=count($res);
   for($i=0;$i<$total;$i++)
     $this->send($res[$i]['API_ID'], $res[$i]['PHONE'], $message);	 
}

function sendNotifByName($name,$message)
{    
    $query = "SELECT * FROM smsru_list WHERE TITLE='".$name."'";
    $res=SQLSelect($query); 

    foreach ($res as $row)
      $this->send($row['API_ID'], $row['PHONE'], $message);
}

function processSubscription($event, $details='') {
  $this->getConfig();
  if ($event=='SAY') {
    $level=$details['level'];
    $message=$details['message'];
  
    $res=SQLSelect("SELECT * FROM smsru_list WHERE ACTIVE=1 AND level<=".$level);
	 
    $total=count($res);
    for($i=0;$i<$total;$i++)
      $this->send($res[$i]['API_ID'], $res[$i]['PHONE'], $message);		
  }
}
 
 /**
* search
*
* @access public
*/
 function search(&$out) {
   require(DIR_MODULES.$this->name.'/search.inc.php');
 }

/**
* edit/add
*
* @access public
*/
 function edit(&$out, $id) {
  require(DIR_MODULES.$this->name.'/edit.inc.php');
 } 
 /**
* delete record
*
* @access public
*/
 function delete($id) {
  $rec=SQLSelectOne("SELECT * FROM smsru_list WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM smsru_list WHERE ID='".$rec['ID']."'");
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
function install($data='') {
  subscribeToEvent($this->name, 'SAY');
  parent::install();
}	

function dbInstall() {	 
	   $data = <<<EOD
 smsru_list: ID int(10) unsigned NOT NULL auto_increment
 smsru_list: TITLE varchar(100) NOT NULL DEFAULT ''
 smsru_list: API_ID varchar(100) NOT NULL DEFAULT '' 
 smsru_list: PHONE varchar(100) NOT NULL DEFAULT '' 
 smsru_list: LEVEL  int(10) NOT NULL DEFAULT 0 
 smsru_list: ACTIVE int(3) DEFAULT 1  
 
EOD;
  parent::dbInstall($data);
}
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDI3LCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
