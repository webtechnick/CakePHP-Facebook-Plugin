<?php

function get_db_conn() {
  $conn = mysql_connect($GLOBALS['db_ip'], $GLOBALS['db_user'], $GLOBALS['db_pass']);
  mysql_select_db($GLOBALS['db_name'], $conn);
  return $conn;
}

function get_prints($user) {
  $conn = get_db_conn();
  $res = mysql_query('SELECT `from`, `to`, `time` FROM footprints WHERE `to`=' . $user . ' ORDER BY `time` DESC', $conn);
  $prints = array();
  while ($row = mysql_fetch_assoc($res)) {
    $prints[] = $row;
  }
  return $prints;
}

function render_profile_action($id, $num) {
  return '<fb:profile-action url="http://apps.facebook.com/footprints/?to=' . $id . '">'
       .   '<fb:name uid="' . $id . '" firstnameonly="true" capitalize="true"/> '
       .   'has been stepped on ' . $num . ' times.'
       . '</fb:profile-action>';
}

function render_profile_box($id, $prints) {
  // Render the most recent 5 no matter what, and the second most recent 5
  // only if the box is on the right (wide) side of the profile.
  return render_prints($prints, 5) . '<fb:wide>' . render_prints(array_slice($prints, 5), 5) . '</fb:wide>'
       . '<div style="clear: both;">' . render_step_link($to) . '</div>';
}

function do_step($from, $to) {
  global $facebook;

  $conn = get_db_conn();
  mysql_query('INSERT INTO footprints SET `from`='.$from.', `time`='.time().', `to`='.$to, $conn);
  $prints = get_prints($to);
  try {
    
    // Set Profile FBML
    $fbml = render_profile_action($to, count($prints)) . render_profile_box($to, $prints);

    // start batch operation 
    $facebook->api_client->begin_batch();

    $facebook->api_client->profile_setFBML($fbml, $to);

    // Send notification
    // Notice the use of reference '&'
    $result = & $facebook->api_client->notifications_send($to, ' stepped on you.  ' .
      '<a href="http://apps.facebook.com/footprints/">See all your Footprints</a>.');

    // Publish feed story
    $feed_title = '<fb:userlink uid="'.$from.'" shownetwork="false"/> stepped on <fb:name uid="'.$to.'"/>.';
    $feed_body = 'Check out <a href="http://apps.facebook.com/footprints/?to='.$to.'">' .
                 '<fb:name uid="'.$to.'" firstnameonly="true" possessive="true"/> Footprints</a>.';
    $facebook->api_client->feed_publishActionOfUser($feed_title, $feed_body);

    // End batch operation. This will actually send queued API call to Facebook in
    // a single HTTP request
    $facebook->api_client->end_batch();

  } catch (Exception $e) {
    error_log($e->getMessage());
  }
  return $prints;
}

function render_step_link($id) {
  return '<a href="http://apps.facebook.com/footprints/?to=' . $id . '">'
       .   'Step on <fb:name uid="' . $id . '" firstnameonly="true"/>'
       . '</a>';
}

function render_prints($prints, $max) {
  $fbml = '';
  $i = 0;
  foreach ($prints as $post) {
    $fbml .= '<fb:if-can-see uid="' . $post['from'] . '"><div style="clear: both; padding: 3px;">'
           .   '<fb:profile-pic style="float: left;" uid="' . $post['from'] . '" size="square"/>'
           .   '<fb:name uid="' . $post['from'] . '" capitalize="true"/> stepped on <fb:name uid="' . $post['to'] . '"/>'
           .   ' at <fb:time t="' . $post['time'] . '"/>. '
           .   '<br/>' . render_step_link($post['from']) . '<br/>'
           . '</div></fb:if-can-see>';
    if (++$i == $max) break;
  }
  return $fbml;
}
