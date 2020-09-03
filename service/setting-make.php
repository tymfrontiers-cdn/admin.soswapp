<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
\require_login(false);
\check_access("/setting-option", false, "project-admin");

$errors = [];
$gen = new Generic;
$required = ["key","domain"];
$pre_params = [
  "id"    => ["id","int",1,0],
  "key"    => ["key","username",3,32,[],'UPPER',['-','.']],
  "domain"          => ["domain","username",3,72,[],'LOWER',['-','.']],
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
// echo "<tt> <pre>";
// \print_r($params);
// echo "</pre></tt>";
// exit;
$set = false;
$key_prop = false;
if( $params ):
  if( !empty($params['key']) && !empty($params["domain"]) ){
    if (!empty($params['id'])) {
      $set = (new MultiForm(MYSQL_BASE_DB,'setting','id'))->findBySql("SELECT id, skey AS 'key', sval AS 'value' FROM :db:.:tbl: WHERE id={$params['id']} LIMIT 1");
      if (!$set) {
        $errors[] = "No setting record found for ID: {$params['id']}.";
      } else {
        $set = $set[0];
      }
    }
    $key_prop = (new MultiForm(MYSQL_BASE_DB,'setting_option','id'))
    ->findBySql("SELECT name, type, type_variant, title, description
                 FROM :db:.:tbl:
                 WHERE name='{$database->escapeValue($params['key'])}'
                 AND domain='{$database->escapeValue($params['domain'])}'
                 LIMIT 1");
   if ($key_prop) {
     $key_prop = $key_prop[0];
   } else {
     $errors[] = "Setting option not found for {$params['domain']}\\{$params['key']}";
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
    <div class="grid-8-tablet grid-6-laptop center-tablet">
      <div class="sec-div color blue bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1> <i class="fas fa-cogs"></i> Setting</h1>
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
            <h4 class="margin -mnone"><?php echo $key_prop->title; ?></h4>
            <p>
              <code class="padding -p5"><?php echo "{$params['domain']}\\{$params['key']}"; ?></code> <br>
              <i><?php echo $key_prop->description; ?></i>
            </p>
            <form
            id="setting-form"
            class="block-ui"
            method="post"
            action="/app/tymfrontiers-cdn/admin.soswapp/src/MakeSetting.php"
            data-validate="false"
            onsubmit="sos.form.submit(this,doSave);return false;"
            >
            <input type="hidden" name="id" value="<?php echo $set ? $set->id : ''; ?>">
            <input type="hidden" name="key" value="<?php echo $params["key"]; ?>">
            <input type="hidden" name="domain" value="<?php echo $params["domain"]; ?>">
            <input type="hidden" name="form" value="setting-form">
            <input type="hidden" name="CSRF_token" value="<?php echo ($session->createCSRFtoken("setting-form"));?>">
          <?php $valid = new \TymFrontiers\Validator;
                $typev = empty($key_prop->type_variant) ? false : Helper\setting_variant($key_prop->type_variant);
                if (\in_array($key_prop->type, ["name", "username"])) { ?>
            <div class="grid-12-tablet">
              <label for="value"> <i class="fas fa-asterisk fa-sm fa-border"></i> <b>Value</b> (<?php echo $valid->validate_type[$key_prop->type]; ?>)</label>
              <input
                type="text"
                <?php echo @ $typev && (int)$typev["valval"] > 0 ? " minlength=\"{$typev["minval"]}\"" : "" ?>
                <?php echo @ $typev && (int)$typev["maxval"] > 0 ? " maxlength=\"{$typev["maxval"]}\"" : "" ?>
                name="value"
                id="value" required
                autocomplete="off"
                value="<?php echo $set ? $set->value : ''; ?>"
                placeholder="Enter <?php echo $valid->validate_type[$key_prop->type]; ?>"
              >
            </div>
          <?php } else if ($key_prop->type == "option") {
                if ($typev && !empty($typev['options']) && !empty($typev['optiontype']) && \in_array($typev["optiontype"],["radio", "checkbox"])) {?>
            <div class="grid-12-tablet">
              <label> <i class="fas fa-asterisk fa-sm fa-border"></i> <b>Value</b> (<?php echo $valid->validate_type[$key_prop->type]; ?>)</label> <br>
              <?php $set_opt_vals = $set ? \explode(",",$set->value) : ""; foreach ($typev["options"] as $index => $value): ?>
                <input
                  type="<?php echo $typev["optiontype"]; ?>"
                  name="value"
                  value="<?php echo $value; ?>"
                  id="<?php echo "option-{$index}"; ?>"
                  <?php echo ($set && \in_array($value,$set_opt_vals)) || (!$set && $index==0) ? " checked " : "" ?>
                >
                <label for="<?php echo "option-{$index}"; ?>"> <?php echo $value; ?></label>
              <?php endforeach; ?>
            </div>
          <?php } } elseif (\in_array($key_prop->type, ["email","tel","url","password"])) { ?>
            <div class="grid-12-tablet">
              <label for="value"> <i class="fas fa-asterisk fa-sm fa-border"></i> <b>Value</b> (<?php echo $valid->validate_type[$key_prop->type]; ?>)</label>
              <input
                type="<?php echo $key_prop->type; ?>"
                name="value"
                id="value" required
                value="<?php echo $set ? $set->value : ''; ?>"
                placeholder="Enter <?php echo $valid->validate_type[$key_prop->type]; ?>"
                autocomplete="off"
              >
            </div>
          <?php } elseif (\in_array($key_prop->type, ["text","ip"])) { ?>
            <div class="grid-12-tablet">
              <label for="value"> <i class="fas fa-asterisk fa-sm fa-border"></i> <b>Value</b> (<?php echo $valid->validate_type[$key_prop->type]; ?>)</label>
              <input
                type="text"
                name="value"
                <?php if ($key_prop->type !== "ip"): ?>
                  <?php echo @ $typev && (int)$typev["minval"] > 0 ? " minlength=\"{$typev["minval"]}\"" : "" ?>
                  <?php echo @ $typev && (int)$typev["maxval"] > 0 ? " maxlength=\"{$typev["maxval"]}\"" : "" ?>
                <?php endif; ?>
                autocomplete="off"
                id="value" required
                value="<?php echo $set ? $set->value : ''; ?>"
                placeholder="Enter <?php echo $valid->validate_type[$key_prop->type]; ?>"
              >
            </div>
          <?php } elseif (\in_array($key_prop->type, ["html","markdown","mixed","script"])) { ?>
            <div class="grid-12-tablet">
              <label for="value"> <i class="fas fa-asterisk fa-sm fa-border"></i> <b>Value</b> (<?php echo $valid->validate_type[$key_prop->type]; ?>)</label>
              <textarea
                name="value"
                <?php echo @ $typev && (int)$typev["minval"] > 0 ? " minlength=\"{$typev["minval"]}\"" : "" ?>
                <?php echo @ $typev && (int)$typev["maxval"] > 0 ? " maxlength=\"{$typev["maxval"]}\"" : "" ?>
                id="value" required
                class="autosize"
                placeholder="Enter <?php echo $valid->validate_type[$key_prop->type]; ?>"
              ><?php echo $set ? $set->value : ''; ?></textarea>
            </div>
          <?php } elseif ($key_prop->type == "boolean") { ?>
            <div class="grid-12-tablet">
              <label> <i class="fas fa-asterisk fa-sm fa-border"></i> <b>Value</b> (<?php echo $valid->validate_type[$key_prop->type]; ?>)</label> <br>
                <input
                  type="radio"
                  name="value"
                  value="1"
                  id="bool-true"
                  <?php echo ($set && (bool)$set->value ===  true) || !$set ? " checked " : "" ?>
                >
                <label for="bool-true"> True</label>
                <input
                  type="radio"
                  name="value"
                  value="0"
                  id="bool-false"
                  <?php echo ($set && (bool)$set->value ===  false) ? " checked " : "" ?>
                >
                <label for="bool-false"> False</label>
            </div>
          <?php } elseif (\in_array($key_prop->type, ["date","time","datetime"])) { ?>
            <div class="grid-12-tablet">
              <label for="value"> <i class="fas fa-asterisk fa-sm fa-border"></i> <b>Value</b> (<?php echo $valid->validate_type[$key_prop->type]; ?>)</label>
              <input
                name="value"
                <?php if (\in_array($key_prop->type, ["date","datetime"])): ?>
                  <?php echo @ $typev && !empty($typev["minval"]) > 0 ? " min=\"{$typev["minval"]}\"" : "" ?>
                  <?php echo @ $typev && !empty($typev["maxval"]) > 0 ? " max=\"{$typev["maxval"]}\"" : "" ?>
                <?php else: ?>
                  <?php echo @ $typev && !empty($typev["minval"]) > 0 ? " min=\"{$typev["minval"]}\"" : "" ?>
                  <?php echo @ $typev && !empty($typev["maxval"]) > 0 ? " max=\"{$typev["maxval"]}\"" : "" ?>
                <?php endif; ?>
                id="value" required
                value="<?php echo $set ? $set->value : ''; ?>"
                type="<?php echo $key_prop->type; ?>"
                placeholder="Enter <?php echo $valid->validate_type[$key_prop->type]; ?>"
              >
            </div>
          <?php } elseif (\in_array($key_prop->type, ["int","float"])) { ?>
            <div class="grid-12-tablet">
              <label for="value"> <i class="fas fa-asterisk fa-sm fa-border"></i> <b>Value</b> (<?php echo $valid->validate_type[$key_prop->type]; ?>)</label>
              <input
                name="value"
                <?php echo @ $typev && !empty($typev["minval"]) > 0 ? " min=\"{$typev["minval"]}\"" : "" ?>
                <?php echo @ $typev && !empty($typev["maxval"]) > 0 ? " max=\"{$typev["maxval"]}\"" : "" ?>
                id="value" required
                value="<?php echo $set ? $set->value : ''; ?>"
                type="number"
                <?php echo $key_prop->type == "float" ? " step='any' " : ""; ?>
                placeholder="Enter <?php echo $valid->validate_type[$key_prop->type]; ?>"
              >
            </div>
          <?php } else {
            echo "<p>Invalid setting configuration, contact Developer.</p>";
          }?>
            <div class="grid-5-tablet">
              <button id="submit-form" type="submit" class="btn blue"> <i class="fas fa-save"></i> Save </button>
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
    $('textarea.autosize').autosize();
  })();
</script>
