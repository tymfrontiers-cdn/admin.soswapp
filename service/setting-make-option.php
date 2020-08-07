<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
\require_login(false);
\check_access("/setting-option", false, "project-admin");

$errors = [];
$gen = new Generic;
$option = false;
$required = [];
$pre_params = [
  "id"    => ["id","int",1,0],
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
if( $params ):
  if( !empty($params['id']) ){
    $option = (new MultiForm(MYSQL_BASE_DB,'setting_option','id'))->findById($params['id']);

    if( !$option ){
      $errors[] = "No record found for given ID: [{$params['id']}]";
    }
  }
endif;
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
    <div class="grid-12-tablet grid-8-laptop center-tablet">
      <div class="sec-div color blue bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1> <i class="fas fa-cogs"></i> Setting option</h1>
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
            id="setting-option-form"
            class="block-ui"
            method="post"
            action="/app/tymfrontiers-cdn/admin.soswapp/src/MakeSettingOption.php"
            data-validate="false"
            onsubmit="sos.form.submit(this,doSave);return false;"
            >
            <input type="hidden" name="id" value="<?php echo $option ? $option->id : ""; ?>">
            <input type="hidden" name="form" value="setting-option-form">
            <input type="hidden" name="CSRF_token" value="<?php echo ($session->createCSRFtoken("setting-option-form"));?>">

            <div class="grid-6-tablet">
              <lable><i class="fas fa-asterisk fa-sm fa-border"></i> Domain</lable>
              <select name="domain" required>
                <option value="">* Choose a work domain</option>
              <?php if ($domains = (new MultiForm(MYSQL_ADMIN_DB,'work_domain','name'))->findAll()) {
                foreach ($domains as $domain) {
                  echo " <option value=\"{$domain->name}\" title=\"{$domain->info}\" ";
                  echo !$option
                    ? (!empty($params['domain']) && $params['domain'] == $domain->name ? 'selected' : '')
                    : ($option->domain == $domain->name ? 'selected' : '');
                  echo ">{$domain->name}</option>";
                }
              } ?>
              </select>
            </div>
            <div class="grid-6-tablet">
              <lable class="bold"><i class="fas fa-asterisk fa-sm fa-border"></i> Type</lable>
              <select name="type" required>
                <option value="">* Choose option type</option>
              <?php foreach ((new \TymFrontiers\Validator)->validate_type as $type=>$desc) {
                  echo " <option value=\"{$type}\" title=\"{$desc}\" ";
                  echo $option && $option->type == $type ? 'selected' : '';
                  echo ">{$desc}</option>";
                }?>
              </select>
            </div>
            <div class="grid-12-tablet">
              <label for="type_variant"> Type variant</label>
              <input type="text" name="type_variant" maxlength="512" id="type_variant" placeholder="options-:VALUE1-,VALUE2-,VALUE3-;minlen-:34-;maxlen-:235" value="<?php echo $option ? $option->type_variant : ''; ?>">
            </div>
            <div class="grid-8-tablet">
              <label for="name"><i class="fas fa-asterisk fa-sm fa-border"></i> Name</label>
              <input type="text" name="name" maxlength="32" id="name" <?php echo $option ? 'readonly' : ''; ?> placeholder="OPTION-NAME" required value="<?php echo $option ? $option->name : ''; ?>">
            </div>

            <div class="grid-10-tablet">
              <label for="title"><i class="fas fa-asterisk fa-sm fa-border"></i> Title</label>
              <input type="text" name="title" maxlength="52" id="title" placeholder="Title" required value="<?php echo $option ? $option->title : ''; ?>">
            </div>
            <div class="grid-12-tablet">
              <label for="description"><i class="fas fa-asterisk fa-sm fa-border"></i> Description</label>
              <textarea name="description" required maxlength="256" minlegth="5" class="autosize" id="description" placeholder="Path description"><?php echo $option ? $option->description : ''; ?></textarea>
            </div>

            <div class="grid-7-tablet">
              <b>Multple values can be set?</b> <br>
              <input type="checkbox" name="multi_val" value="1" <?php echo !$option || ($option && (bool)$option->multi_val) ? "checked" : ""; ?> id="multi-val">
              <label for="multi-val"> Yes</label>
            </div>
            <div class="grid-6-tablet">
              <button id="submit-form" type="submit" class="btn blue"> <i class="far fa-save"></i> Save </button>
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
          if( typeof window['refreshList'] == 'function' ){
            refreshList(data);
          }
        },1500);
      }
    }
  }
  (function(){
    $('input[name=name]').blur(function(){
      $(this).val($(this).val().replaceAll(' ','.').toUpperCase());
    });
    $('textarea.autosize').autosize();
  })();
</script>
