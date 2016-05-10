<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='smsru_list';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
	 
	 global $api_id;
	 $rec['API_ID']=$api_id;
	 if ($rec['API_ID']=='') {
    $out['ERR_API_ID']=1;
    $ok=0;
   }
	 
   global $phone;
	 $rec['PHONE']=$phone;
	 if ($rec['PHONE']=='') {
    $out['ERR_PHONE']=1;
    $ok=0;
   }

   global $level;
	 $rec['LEVEL']=$level;
	 if ($rec['LEVEL']=='') {
    $out['ERR_LEVEL']=1;
    $ok=0;
   }
  
	 global $active;
	 $rec['ACTIVE']=$active;
   
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }

  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
