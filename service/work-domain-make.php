<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
\require_login(false);
\check_access("/work-domain", false, "project-admin");

$errors = [];
$gen = new Generic;
$domain = false;
$required = [];
$pre_params = [
  "name" => ["name","username",3,35,[],'MIXED', ['-','.', '_']],
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
  if( !empty($params['name']) ){
    $domain = (new MultiForm(MYSQL_ADMIN_DB,'work_domain','name'))->findById($params['name']);

    if( !$domain ){
      $errors[] = "No record found for given domain name [{$params['name']}]";
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
    <div class="grid-10-tablet grid-8-laptop center-tablet">
      <div class="sec-div color blue bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1> <i class="fas fa-globe"></i> Work domain</h1>
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
            id="work-domain-form"
            class="block-ui"
            method="post"
            action="/app/tymfrontiers-cdn/admin.soswapp/src/MakeWorkDomain.php"
            data-validate="false"
            onsubmit="sos.form.submit(this,doSave);return false;"
            >
            <input type="hidden" name="task" value="<?php echo $domain ? 'UPDATE' : 'CREATE'; ?>">
            <input type="hidden" name="form" value="work-domain-form">
            <input type="hidden" name="CSRF_token" value="<?php echo ($session->createCSRFtoken("work-domain-form"));?>">

            <div class="grid-8-tablet">
              <label for="name"><i class="fas fa-asterisk fa-border fa-sm"></i> Domain name</label>
              <input type="text" name="name" maxlength="98" <?php echo $domain ? 'readonly' : ''; ?> id="name" placeholder="domain.com" required value="<?php echo $domain ? $domain->name : ''; ?>">
            </div>
            <div class="grid-4-tablet">
              <label for="acronym"><i class="fas fa-asterisk fa-border fa-sm"></i> Acronym</label>
              <input type="text" name="acronym" maxlength="16" id="acronym" placeholder="ACR" required value="<?php echo $domain ? $domain->acronym : ''; ?>">
            </div>
            <div class="grid-7-tablet">
              <label for="path"><i class="fas fa-asterisk fa-border fa-sm"></i> Admin path</label>
              <input type="text" name="path" maxlength="72" id="path" placeholder="/" required value="<?php echo $domain ? $domain->path : ''; ?>">
            </div>
            <div class="grid-5-tablet">
              <label for="icon"><i class="fas fa-asterisk fa-border fa-sm"></i> Display icon</label>
              <input type="text" name="icon" maxlength="72" id="icon" placeholder="fas fa-globe" required value="<?php echo $domain ? $domain->icon : ''; ?>">
            </div>

            <div class="grid-12-tablet">
              <label for="description"><i class="fas fa-asterisk fa-border fa-sm"></i> Description</label>
              <textarea name="description" maxlength="128" minlegth="5" class="autosize" id="description" placeholder="Domain description" required><?php echo $domain ? $domain->description : ''; ?></textarea>
            </div>


            <div class="grid-4-tablet">
              <br>
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
    $('input[name=acronym]').blur(function(){
      $(this).val($(this).val().replaceAll(' ','').toUpperCase());
    })
    $('textarea.autosize').autosize();
  })();
</script>
