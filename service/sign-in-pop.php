<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

$errors = [];
$gen = new Generic;
$required = [];
$pre_params = [
  "rdt" => ["rdt", "url"]
];
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
          <span class="fa-stack fa-2x push-right color-text">
            <i class="far fa-stack-2x fa-circle"></i>
            <i class="fas fa-stack-1x fa-lock fa-sm"></i>
          </span>
          <h2>Sign in</h2>
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
              id="long-form"
              class="block-ui padding -p20"
              method="post"
              action="/app/tymfrontiers-cdn/admin.soswapp/src/SignIn.php"
              data-validate="false"
              onsubmit="sos.form.submit(this, DoSignIn); return false;"
            >
              <input type="hidden" name="rdt" value="<?php echo !empty($params['rdt']) ? $params['rdt'] : ''; ?>">
              <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken('long-form'); ?>">
              <input type="hidden" name="form" value="long-form">
              <div class="grid-12-phone">
                <label for="email"> <i class="fas fa-asterisk fa-sm fa-border"></i> Email</label>
                <input type="email" placeholder="email@omain.ext" name="email" id="email" autocomplete="email" required>
              </div>
              <div class="grid-9-phone">
                <label for="password"> <i class="fas fa-asterisk fa-sm fa-border"></i> Password</label>
                <input type="password" placeholder="Login Password" name="password" id="password" autocomplete="off" required>
              </div>
              <div class="grid-3-phone">
                <br>
                <button type="submit" class="sos-btn face-primary"> <i class="fas fa-angle-double-right"></i></button>
              </div>
              <div class="grid-12-tablet">
                <input type="checkbox" class="solid" name="remember" value="1" id="remember-pop">
                <label for="remember-pop" class="bold color-text">Remember me</label>
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
</script>
