<?php

//VARIABLE Overwrite
//set captcha text length, default is 4
  #$GLOBALS['string_length'] = 4;
//set captcha label, default is turned off
  #$GLOBALS['question_txt'] = "Captcha";
//Set Captcha input box placeholder / title
  #$GLOBALS['captcha_placeholder'] = "CAPTCHA";
//Set how many retry before captcha, default is 3, set 0 to always have captcha
  #$GLOBALS['set_retry'] = 6;

//MANDATORY VARIABLES TO SET
//captcha function file
$GLOBALS['functionfile'] = "./all.php";

/*next function to call after valid captcha, add function name only,
* example: to call function validate_user();
* insert 'validate_user' in the variable
* to call a class function set the variable $GLOBALS['className'] = 'your class name';
* and then add the method/function name in the variable $GLOBALS['external_call']
*/
$GLOBALS['className'] = 'security';
$GLOBALS['external_call'] = 'success';

//include captcha function file
require $GLOBALS['functionfile'];

//call captcha input box
//echo captcha_form();

//Can use this to only populate when session has reached the trigger limit
  if (isset($_SESSION['captcha_retry'])) {  echo captcha_form(); }

?>
