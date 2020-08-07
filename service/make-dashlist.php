<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
\require_login(false);
\check_access("/dashlist", false, "project-admin");

$errors = [];
$gen = new Generic;
$dash = false;
$required = [];
$pre_params = [
  "id"          => ["id","int"],
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
    $dash = (new MultiForm(MYSQL_BASE_DB,'user_dashlist','id'))->findById($params['id']);

    if( !$dash ){
      $errors[] = "No record found for given dashlist id [{$params['id']}]";
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
          <h1> <i class="fas fa-bars"></i> Dashlist</h1>
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
            id="dashlist-form"
            class="block-ui"
            method="post"
            action="/app/tymfrontiers-cdn/admin.soswapp/src/MakeDashlist.php"
            data-validate="false"
            onsubmit="sos.form.submit(this,doSave);return false;"
            >
            <input type="hidden" name="id" value="<?php echo $dash ? $dash->id : ''; ?>">
            <input type="hidden" name="form" value="dashlist-form">
            <input type="hidden" name="CSRF_token" value="<?php echo ($session->createCSRFtoken("dashlist-form"));?>">

            <div class="grid-10-tablet">
              <label for="path"><i class="fas fa-asterisk fa-sm fa-border"></i> Full/relative path</label>
              <input type="text" name="path" maxlength="256" id="path" placeholder="/" required value="<?php echo $dash ? $dash->path : ''; ?>">
            </div>

            <div class="grid-7-tablet">
              <label for="title"><i class="fas fa-asterisk fa-sm fa-border"></i> Title</label>
              <input type="text" name="title" maxlength="56" id="title" placeholder="Path title" required value="<?php echo $dash ? $dash->title : ''; ?>">
            </div>
            <div class="grid-8-tablet">
              <label for="subtitle">Subtitle</label>
              <input type="text" name="subtitle" maxlength="72" id="subtitle" placeholder="Optional subtitle" value="<?php echo $dash ? $dash->subtitle : ''; ?>">
            </div>
            <div class="grid-8-tablet">
              <label for="icon">Icon</label>
              <input type="text" name="icon" maxlength="72" id="icon" placeholder="Path icon" value="<?php echo $dash ? $dash->icon : ''; ?>">
            </div>
            <div class="grid-4-phone grid-3-tablet">
              <label for="sort">Sort</label>
              <input type="number" name="sort" id="sort" placeholder="Order pos" value="<?php echo $dash ? $dash->sort : ''; ?>">
            </div>
            <br class="c-f">
            <div class="grid-5-tablet">
              <label for="onclick">OnClick (JS FN)</label>
              <input type="text" name="onclick" maxlength="72" id="onclick" placeholder="functionToCall" value="<?php echo $dash ? $dash->onclick : ''; ?>">
            </div>
            <div class="grid-7-tablet">
              <label for="classname">Class name(s) (CSS)</label>
              <input type="text" name="classname" maxlength="72" id="classname" placeholder="class1 class2" value="<?php echo $dash ? $dash->classname : ''; ?>">
            </div>
            <div class="grid-12-tablet">
              <label for="description"><i class="fas fa-asterisk fa-sm fa-border"></i> Description</label>
              <textarea name="description" required maxlength="256" minlegth="5" class="autosize" id="description" placeholder="Description"><?php echo $dash ? $dash->description : ''; ?></textarea>
            </div>

            <div class="grid-4-tablet">
              <br>
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
