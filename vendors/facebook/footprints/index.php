<?php

// the facebook client library
include_once '../php/facebook.php';

// some basic library functions
include_once 'lib.php';

// this defines some of your basic setup
include_once 'config.php';

$facebook = new Facebook($api_key, $secret);
$facebook->require_frame();
$user = $facebook->require_login();

if (isset($_POST['to'])) {
  $prints_id = (int)$_POST['to'];
  $prints = do_step($user, $prints_id);
} else {
  if (isset($_GET['to'])) {
    $prints_id = (int)$_GET['to'];
  } else {
    $prints_id = $user;
  }
  $prints = get_prints($prints_id);
}

?>
<div style="padding: 10px;">
  <h2>Hi <fb:name firstnameonly="true" uid="<?=$user?>" useyou="false"/>!</h2><br/>
  <a href="<?= $facebook->get_add_url() ?>">Put Footprints in your profile</a>, if you haven't already!
    <form method="post" >
<?php
      if ($prints_id != $user) {
        echo 'Do you want to step on <fb:name uid="' . $prints_id . '"/>?';
        echo '<input type="hidden" name="to" value="' . $prints_id . '"/>';
      } else {
        echo '<br/>Step on a friend:';
        echo '<fb:friend-selector idname="to"/>';
      }
?>
      <input value="step" type="submit"/>
    </form>
  <hr/>
  These are <fb:name uid="<?= $prints_id ?>" possessive="true"/> Footprints:<br/>
  <?php echo render_prints($prints, 10); ?>
  <div style="clear: both;"/>
</div>
