<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
\require_login(false);

$errors = [];
$gen = new Generic;
$required = [];
$pre_params = [
  "callback" => ["callback","username",3,35,[],'MIXED']
];
// if( empty($_GET['id']) ) $required[] = 'owner';
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
?>
<input
  type="hidden"
  id="rparam"
  <?php if($params){ foreach($params as $k=>$v){
    echo "data-{$k}=\"{$v}\" ";
  } }?>
  >
<div id="fader-flow">
  <div class="view-space">
    <div class="padding -p20">&nbsp;</div>
    <br class="c-f">
    <div class="grid-7-tablet grid-5-desktop center-tablet">
      <div class="sec-div color face-secondary bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1> <i class="fas fa-key"></i> Change login password</h1>
        </header>

        <div class="padding -p20">
          <?php if(!empty($errors)){ ?>
            <h3>Unresolved error(s)</h3>
            <ol>
              <?php foreach($errors as $err){
                echo " <li>{$err}</li>";
              } ?>
            </ol>
          <?php }else{ ?>
            <form
            id="set-password-form"
            class="block-ui"
            method="post"
            action="/src/SetPassword.php"
            data-validate="false"
            onsubmit="sos.form.submit(this,pwdSaved);return false;"
            >
            <input type="hidden" name="form" value="set-password-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("set-password-form");?>">

            <div class="grid-11-phone">
              <label for="password-old"><i class="fas fa-asterisk fa-border fa-sm"></i>  Current Password</label>
              <input type="password" autocomplete="current-password" name="password_old" id="password-old" placeholder="OldPassword" required>
            </div>
            <div class="grid-12-tablet">
              <h4>Create a strong password</h4>
              <p>A strong password should contain alpha-numeric multi-case combination such as: <b> <i>PassWD@12*4</i></b>, between 8-16 characters long.</p>
              <ul>
                <li>Allowed special characters/symbols include: <b>$ @ $ ! % * ? &</b></li>
                <li>Do not use above sample as your password!</li>
                <li>For extra security, it's advised not to use your name, phone number, date of birth as password</li>
              </ul>
            </div>
            <div class="grid-10-phone">
              <label for="password"><i class="fas fa-asterisk fa-border fa-sm"></i> New Password</label>
              <input type="password" placeholder="Password" autocomplete="new-password" name="password" id="password"  required>
            </div>
            <div class="grid-8-phone">
              <label for="password-repeat"><i class="fas fa-asterisk fa-border fa-sm"></i>  Repeat new password</label>
              <input type="password" placeholder="Password" autocomplete="new-password" name="password_repeat" id="password-repeat" required>
            </div>
            <div class="grid-7-phone grid-4-tablet">
              <br>
              <button id="submit-form" type="submit" class="btn face-secondary"> <i class="fas fa-save"></i> Save </button>
            </div>

            <br class="c-f">
          </form>
        <?php } ?>
      </div>
    </div>
  </div>
  <br class="c-f">
</div>
</div>

<script type="text/javascript">
  var param = $('#rparam').data();
  function pwdSaved(data){
    if( data && data.status == '00' || data.errors.length < 1 ){
      if( ('callback' in param) && typeof window[param.callback] == 'function' ){
        faderBox.close();
        window[param.callback](data);
      }else{
        setTimeout(function(){
          faderBox.close();
          removeAlert();
        },1500);
      }
    }
  }
  (function(){
  })();
</script>
