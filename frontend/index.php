<?php
function mcsignup($action, ...$args)
{
  $argstr = implode(' ', $args);
  $resp=`/home/isaac/mcshell-signup/mc-signup-{$action} {$argstr}`;
  $resp = trim($resp);
  $resp = explode(':', $resp);
  $result = array();
  $result['success'] = $resp[0] == 'SUCCESS';
  if(count($resp) >= 2) {
    $result['message'] = $resp[1];
  } else {
    $result['message'] = '';
  }

  return $result;
}

//default state
$errors = array();
$mode = 'email';

//extract username
if(isset($_REQUEST['email'])) {
    $user = explode('@', $_REQUEST['email']);
    $user = $user[0];
}


if(isset($_REQUEST['request_password'])) {
   $resp=mcsignup('request', $_REQUEST['email']);
   if($resp['success']) {
       //mark for success and send the email
       $mode='email_success';
       $token = urlencode($resp['message']);
       $email = urlencode($_REQUEST['email']);
       $body = "To set your password for your Maryville College CS Server account, please visit: https://cs.maryvillecollege.edu/signup/index.php?token={$token}&email={$email}";
       mail($_REQUEST['email'], 'Maryville College CS Account Registration / Password Reset', $body, 'From: Robert Lowe <robert.lowe@maryvillecollege.edu>');
   } else {
       $errors[] = $resp['message'];
   }
} elseif(isset($_REQUEST['password_set'])) {
  if($_REQUEST['pass1'] != $_REQUEST['pass2']) {
    $errors[] = 'Passwords do not match.  Please Try Again.';
     $mode='password';
  } else {
    $resp=mcsignup('passwd', $_REQUEST['email'], $_REQUEST['token'], $_REQUEST['pass1']);
    if($resp['success']) {
      $mode = 'password_success';
    } else {
      $mode = 'email';
      $errors[]=$resp['message'];      
    }
  }
} elseif(isset($_REQUEST['token'])) {
  $mode='password';
}
?>

<html>
  <body>
    <h2>Maryville College CS Server Account Management</h2>
    <?php if(count($errors)): ?>
      <div class="errors">
        <ul>
        <?php foreach($errors as $error): ?>
	  <li><?php echo $error; ?>
	<?php endforeach; ?>
	</ul>
      </div>
    <?php endif; ?>
  <?php if($mode == 'email'): ?>
    Enter your Maryville College email address to create your account or reset your password.<br/>
    <form action="index.php" method="post">
    Email: <input type="text" name="email"/><input type="submit" name="request_password" value="submit"/>
    </form>
  <?php elseif($mode == 'email_success'): ?>
    An email has been sent to <?php echo $_REQUEST['email']; ?>.  Follow the link to complete your signup/password reset.
  <?php elseif($mode == 'password'): ?>
    <form action="index.php" method="post">
    <input type="hidden" name="token" value="<?php echo $_REQUEST['token']; ?>"/>
    <input type="hidden" name="email" value="<?php echo $_REQUEST['email'];?>"/>
    <table>
    <tr>
      <td>Username:</td>
      <td><?php echo $user; ?></td>
    </tr>
    <tr>
      <td>Password</td>        
      <td><input type="password" name="pass1"/></td>
    </tr>
    <tr>
      <td>Confirm</td>
      <td><input type="password" name="pass2"/></td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <input type="submit" value="Set Password" name="password_set" />
      </td>
    </tr>
    </table>
  <?php elseif($mode == 'password_success'): ?>
  Success!  You have set the password for user <?php echo $user; ?>.  You may now log in to your shell account and/or <a href="https://rstudio.cs.maryvillecollege.edu">RStudio</a>.
  <?php endif; ?>
  </body>
</html>