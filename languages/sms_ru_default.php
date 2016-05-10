<?php
/**
* English language file for NUT module
*
*/

$dictionary=array(

/* general */
'LEVEL'=>'Level',
'PHONE_INFO'=>'login sms.ru'
/* end module names */

);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
} 