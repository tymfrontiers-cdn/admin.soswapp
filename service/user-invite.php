<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
\require_login(false);
\check_access("/user", false, "project-admin");

$errors = [];
$gen = new Generic;
$domain = false;
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
try {
  $location = new Location();
} catch (\Exception $e) {
  // die($e->getMessage());
  $location = false;
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
    <div class="grid-10-tablet grid-8-laptop center-tablet">
      <div class="sec-div color blue bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1 class="fw-lighter"> <i class="fas fa-user"></i> Admin user</h1>
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
            id="user-invite-form"
            class="block-ui"
            method="post"
            action="/app/tymfrontiers-cdn/admin.soswapp/src/InviteUser.php"
            data-validate="false"
            onsubmit="sos.form.submit(this,doSave);return false;"
            >
            <input type="hidden" name="form" value="user-invite-form">
            <input type="hidden" name="CSRF_token" value="<?php echo ($session->createCSRFtoken("user-invite-form"));?>">

            <div class="grid-5-tablet">
              <label><i class="fas fa-asterisk fa-border fa-sm"></i> Country</label>
              <select name="country_code" required>
                <option value="">Choose a country</option>
              <?php if ($countries = (new MultiForm(MYSQL_DATA_DB, 'country', 'code'))->findAll() ) {
                  foreach ($countries as $country) {
                    echo " <option value='{$country->code}'";
                    echo ($location && ($location->country_code == $country->code))
                      ? " selected "
                      : "";
                    echo ">{$country->name}</option>";
                  }
                }
               ?>
              </select>
            </div>
            <div class="grid-5-tablet">
              <label><i class="fas fa-asterisk fa-border fa-sm"></i> Region</label>
              <select name="state_code" required>
                <option value="">Choose a state</option>
                <optgroup label="States">
                  <?php if (($location && !empty($location->country_code)) && $states = (new MultiForm(MYSQL_DATA_DB, 'state', 'code'))->findBySql("SELECT * FROM :db:.:tbl: WHERE country_code='{$location->country_code}' ORDER BY  LOWER(`name`) = '-other', `name` ASC ") ) {
                    foreach ($states as $state) {
                      echo " <option value='{$state->code}'";
                      echo ($location && ($location->state_code == $state->code))
                      ? " selected "
                      : "";
                      echo ">{$state->name}</option>";
                    }
                  }
                  ?>
                </optgroup>
              </select>
            </div>
            <div class="grid-6-tablet">
              <label for="name"><i class="fas fa-asterisk fa-border fa-sm"></i> Name</label>
              <input type="text" name="name" autocomplete="given-name" id="name" placeholder="Name" required>
            </div>
            <div class="grid-6-tablet">
              <label for="surname"><i class="fas fa-asterisk fa-border fa-sm"></i> Surname</label>
              <input type="text" name="surname" autocomplete="family-name" id="surname" placeholder="Surname" required>
            </div>
            <div class="grid-6-tablet">
              <label for="email"><i class="fas fa-asterisk fa-border fa-sm"></i> Email address</label>
              <input type="email" name="email" autocomplete="email" id="email" placeholder="myname@website.com" required>
            </div>
            <div class="grid-5-tablet">
              <label for="phone"><i class="fas fa-asterisk fa-border fa-sm"></i> Phone number</label>
              <input type="tel" name="phone" autocomplete="tel-local" id="phone" required placeholder="0801 234 5678">
            </div>
            <div class="grid-12-tablet">
              <label for="note">Invitation note</label>
              <textarea id="note" maxlength="512" class="autosize" name="note" placeholder="Any note/message for the new user?"></textarea>
            </div>
            <div class="grid-5-tablet">
              <label><i class="fas fa-asterisk fa-border fa-sm"></i> Work group</label>
              <select name="work_group" required>
              <?php if ($work_group = (new MultiForm(MYSQL_ADMIN_DB,'work_group','name'))->findBySql("SELECT * FROM :db:.:tbl: WHERE `rank` <= {$database->escapeValue($session->access_rank)} AND `rank` > 0 ORDER BY `rank` ASC")) {
                foreach ($work_group as $wg) {
                  echo " <option value=\"{$wg->name}\"";
                  echo ">{$wg->name}</option>";
                }
             } ?>
              </select>
            </div>
            <div class="grid-4-tablet">
              <br>
              <button id="submit-form" type="submit" class="btn blue"> <i class="fas fa-plus"></i> Invite </button>
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
  function doSave(data){
    if( data && data.status == '0.0' || data.errors.length < 1 ){
      if( ('callback' in param) && typeof window[param.callback] == 'function' ){
        faderBox.close();
        window[param.callback](data);
      }else{
        setTimeout(function(){
          faderBox.close();
          removeAlert();
          if( data.rdt ){
            window.location = data.rdt;
          }
        },1500);
      }
    }
  }
  (function(){
    $('textarea.autosize').autosize();
    $('select[name=country_code]').change(function(){
      if( $(this).val().length > 0 ){
        $('select[name=state_code]').fetchLocal({type:'state',code:$(this).val()});
      }
    });
  })();
</script>
