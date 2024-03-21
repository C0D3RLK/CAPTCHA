<?php
// captcha module
//v 1.0.0 | 15032024
session_start();

//Declaration
// $GLOBALS['external_call'] = 'success';
// $GLOBALS['bypasss_var'] = true;
// $GLOBALS['fonts']  = array(
//   "./fonts/Roboto-Regular.ttf",
//   "./fonts/PrincessSofia-Regular.ttf"
// );
// $GLOBALS['string_length'] = 6;
// $GLOBALS['question_txt'] = "CAPTCHA";


function GENERAL_VARIABLES(){

  $GLOBALS['string_length'] = 4;
  if (isset($_GET['string_length'])) {
    $GLOBALS['string_length'] = $_GET['string_length'];
  }
  $GLOBALS['fonts']  = array(
    "./fonts/Roboto-Regular.ttf",
    // "./fonts/JacquardaBastarda9-Regular.ttf",
    // "./fonts/ablammo-Regular-VariableFont_MORF.ttf"
    "./fonts/PrincessSofia-Regular.ttf"
  );
  $GLOBALS['question_txt'] = "CAPTCHA";
  if (isset($_GET['question_txt'])) {
    $GLOBALS['question_txt'] = $_GET['question_txt'];
  }
  // $GLOBALS['functionfile'] = "./all.php";

    $GLOBALS['functionfile'] = basename($_SERVER['PHP_SELF']);
    $GLOBALS['bypasss_var'] = false;
}

if (isset($_SESSION['captcha_retry']) && isset($_POST['captcha_challenge'])) {
  // $_SESSION['captcha_retry'] = $_SESSION['captcha_retry'] + 1;
  if ( isset($_POST['captcha_challenge']) && $_POST['captcha_challenge'] == $_SESSION['captcha_text'] && $_SERVER['REQUEST_METHOD'] === 'POST')  {
    unset($_SESSION['CAPTCHA_CHECK']);
    unset($_SESSION['captcha_retry']);
    unset($_SESSION['captcha_challenge']);
    // $_SESSION['CAPTCHA_CHECK'] = true;
    #Call system post function
    return external_call();
  }else{
    $_SESSION['CAPTCHA_CHECK'] = false;
  }

}
else{
  if ( $_SERVER['REQUEST_METHOD'] === 'POST' &&  $_SESSION['captcha_retry'] < 3 ) {
    $_SESSION['captcha_retry'] = $_SESSION['captcha_retry'] + 1;
    return external_call();
  }
}

if (isset($_GET['type'])) {
  //captcha code lended from https://code.tutsplus.com/build-your-own-captcha-and-contact-form-in-php--net-5362t
  // if ($GLOBALS['bypasss_var'] == false) {
  GENERAL_VARIABLES();
  // }

  $TEXT_LIBRARY = 'ABCDEFGHJKLMNPQRSTUVWXYZ1234567890';

  function secure_generate_string($input, $strength = 5, $secure = false) {
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < $strength; $i++) {
      if($secure) {
        $random_character = $input[random_int(0, $input_length - 1)];
      } else {
        $random_character = $input[mt_rand(0, $input_length - 1)];
      }
      $random_string .= $random_character;
    }

    return $random_string;
  }

  $image = imagecreatetruecolor(200, 50);
  imageantialias($image, true);
  $colors = [];
  $red = rand(125, 175);
  $green = rand(125, 175);
  $blue = rand(125, 175);
  for($i = 0; $i < 5; $i++) {
    $colors[] = imagecolorallocate($image, $red - 20*$i, $green - 20*$i, $blue - 20*$i);
  }
  imagefill($image, 0, 0, $colors[0]);
  for($i = 0; $i < 10; $i++) {
    imagesetthickness($image, rand(2, 10));
    $line_color = $colors[rand(1, 4)];
    imagerectangle($image, rand(-10, 190), rand(-10, 10), rand(-10, 190), rand(40, 60), $line_color);
  }
  $black = imagecolorallocate($image, 0, 0, 0);
  $white = imagecolorallocate($image, 255, 255, 255);
  $textcolors = [$black, $white];
  // $fonts = [dirname(__FILE__).'/fonts/Roboto-Regular.ttf'];
  $fonts = $GLOBALS['fonts'];
  $string_length = $GLOBALS['string_length'] ;

  $captcha_string = secure_generate_string($TEXT_LIBRARY, $string_length);
  $_SESSION['captcha_text'] = $captcha_string;
  for($i = 0; $i < $string_length; $i++) {
    $letter_space = 170/$string_length;
    $initial = 15;

    imagettftext($image, 24, rand(-15, 15), $initial + $i*$letter_space, rand(25, 45), $textcolors[rand(0, 1)],  $fonts[array_rand($fonts)], $captcha_string[$i]);
  }
  header('Content-type: image/png');
  imagepng($image);
  imagedestroy($image);

}

function captcha_form(){

  $CAPTCHA_STATUS_MSG = (isset($_SESSION['CAPTCHA_CHECK']) && $_SESSION['CAPTCHA_CHECK'] == false)? 'WRONG CAPTCHA': "";

  $SET_RETRY = 3;
  if (isset($GLOBALS['set_retry'] )) {
    $SET_RETRY = $GLOBALS['set_retry'];
  }

  if (!isset($_GET['type']) && $_SESSION['captcha_retry'] >= $SET_RETRY ):

    $SET_PARAMS = "?type=app";
    if (isset($GLOBALS['string_length'] )) {
      $SET_PARAMS = $SET_PARAMS."&string_length=".$GLOBALS['string_length'];
    }
    if (isset($GLOBALS['question_txt'] )) {
      $SET_PARAMS = $SET_PARAMS ."&question_txt=".$GLOBALS['question_txt'];
    }

    if (!isset($GLOBALS['captcha_placeholder'] )) {
      $GLOBALS['captcha_placeholder'] = "insert CAPTCHA";
    }

    $FORM = '
    <div class="elem-group">
    <img src="'.$GLOBALS['functionfile'].$SET_PARAMS.'" alt="CAPTCHA" class="captcha-image"><a class="btn btn-mute btn-primary"><i class="fa-solid fa-arrow-rotate-right refresh-captcha""></i></a>
    <label class="text-danger h4">'.$CAPTCHA_STATUS_MSG.'</label>
    <p><label for="captcha">'.$GLOBALS['question_txt'].'</label>
    <br>
    <input placeholder="'.$GLOBALS['captcha_placeholder'].'" type="text" id="captcha" class="form-control required" name="captcha_challenge" required>
    </div>
    <script type="text/javascript">
    var refreshButton = document.querySelector(".refresh-captcha");
    refreshButton.onclick = function() {
      document.querySelector(".captcha-image").src = "'.$GLOBALS['functionfile'].$SET_PARAMS .'&" + Date.now();
    }
    </script>
    ';
    unset($_SESSION['CAPTCHA_CHECK']);
    return $FORM;
  endif;
}

function external_call(){

  if (function_exists($GLOBALS['external_call'])) {
    return $GLOBALS['external_call']();
  }
  if (class_exists($GLOBALS['className'])) {
      $CAPTCHA = new $GLOBALS['className'];
    return $CAPTCHA->$GLOBALS['external_call']();
  }
  return false;

}

// GENERAL_VARIABLES();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['captcha_retry'] = $_SESSION['captcha_retry'] + 1;
  if (!isset($_SESSION['captcha_retry'])) {
    $_SESSION['captcha_retry']  = 0;
  }
}

?>
