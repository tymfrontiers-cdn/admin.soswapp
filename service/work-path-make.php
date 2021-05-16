<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
\require_login(false);
\check_access("/work-path", false, "project-admin");

$errors = [];
$gen = new Generic;
$path = false;
$required = [];
$pre_params = [
  "name"          => ["name","username",3,32,[],'LOWER'],
  "domain"          => ["domain","username",3,98,[],'LOWER',['-','.']],
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
    $path = (new MultiForm(MYSQL_ADMIN_DB,'work_path','name'))->findById($params['name']);

    if( !$path ){
      $errors[] = "No record found for given path name [{$params['name']}]";
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
          <h1 class="fw-lighter"> <i class="fas fa-bezier-curve"></i> Work path</h1>
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
            id="work-path-form"
            class="block-ui"
            method="post"
            action="/app/tymfrontiers-cdn/admin.soswapp/src/MakeWorkPath.php"
            data-validate="false"
            onsubmit="sos.form.submit(this,doSave);return false;"
            >
            <input type="hidden" name="name" value="<?php echo $path ? $path->name : ''; ?>">
            <input type="hidden" name="form" value="work-path-form">
            <input type="hidden" name="CSRF_token" value="<?php echo ($session->createCSRFtoken("work-path-form"));?>">

            <div class="grid-7-tablet">
              <h4 class="margin -mnone">Work domain</h4>
              <select name="domain" required>
                <option value="">* Choose a work domain</option>
              <?php if ($domains = (new MultiForm(MYSQL_ADMIN_DB,'work_domain','name'))->findAll()) {
                foreach ($domains as $domain) {
                  echo " <option value=\"{$domain->name}\" title=\"{$domain->info}\" ";
                  echo !$path
                    ? (!empty($params['domain']) && $params['domain'] == $domain->name ? 'selected' : '')
                    : ($path->domain== $domain->name ? 'selected' : '');
                  echo $path && $path->domain !== $domain->name ? ' disabled ' : '';
                  echo ">{$domain->name}</option>";
                }
              } ?>
              </select>
            </div> <br class="c-f">
            <div class="grid-5-tablet">
              <label for="path"><i class="fas fa-asterisk fa-sm fa-border"></i> Path</label>
              <input type="text" name="path" maxlength="72" id="path" <?php echo $path ? 'readonly' : ''; ?> placeholder="/" required value="<?php echo $path ? $path->path : ''; ?>">
            </div>
            <div class="grid-4-tablet">
              <h4 class="margin -mnone">Min Access</h4>
              <select name="access_rank" required>
                <option value="">* Access</option>
              <?php if ($access = (new MultiForm(MYSQL_ADMIN_DB,'work_group','name'))->findBySql("SELECT * FROM :db:.:tbl: WHERE `rank` <={$db->escapeValue($session->access_rank())} ORDER BY `rank` ASC")) {
                foreach ($access as $acs) {
                  echo " <option value=\"{$acs->rank}\" ";
                  echo !$path
                    ? ''
                    : ($path->access_rank == $acs->rank ? 'selected ' : '');
                  echo ">{$acs->name}</option>";
                }
              } ?>
              </select>
            </div>
            <div class="grid-3-tablet">
              <span> <b>Strict access</b></span> <br>
              <input type="checkbox" name="access_rank_strict" value="1" id="ars-ON" <?php echo $path && (bool)$path->access_rank_strict ? "checked" : ""; ?>>
              <label for="ars-ON">Use strict</label>
            </div>

            <br class="c-f">
            <div class="grid-6-tablet">
              <label for="title"><i class="fas fa-asterisk fa-sm fa-border"></i> Title</label>
              <input type="text" name="title" maxlength="56" id="title" placeholder="Path title" required value="<?php echo $path ? $path->title : ''; ?>">
            </div>
            <div class="grid-6-tablet">
              <label for="icon">Icon</label>
              <input type="text" name="icon" maxlength="72" id="icon" placeholder="Path icon" value="<?php echo $path ? $path->icon : ''; ?>">
            </div> <br class="c-f">
            <div class="grid-4-phone grid-3-tablet">
              <label for="sort">Sort</label>
              <input type="number" name="sort" id="sort" placeholder="Order pos" value="<?php echo $path ? $path->sort : ''; ?>">
            </div>
            <div class="grid-6-phone grid-3-tablet">
              <h4 class="margin -mnone">Nav visible</h4>
              <input type="checkbox" id="nav_visible" name="nav_visible" value="1" <?php echo (!$path || ($path && (bool)$path->nav_visible)) ? 'checked' : ''; ?>>
              <label for="nav_visible"> On</label>
            </div>
            <div class="grid-5-tablet">
              <h4 class="margin -mnone">Type (access)</h4>
              <input type="radio" name="type" value="READ" id="type-READ" <?php echo !$path || $path && $path->type == 'READ' ? 'checked' : ''; ?>>
              <label for="type-READ">Read</label>
              <input type="radio" name="type" value="ALTER" id="type-ALTER" <?php echo $path && $path->type == 'ALTER' ? 'checked' : ''; ?>>
              <label for="type-ALTER">Alter</label>
            </div> <br class="c-f">
            <div class="grid-5-tablet">
              <label for="onclick">OnClick (JS FN)</label>
              <input type="text" name="onclick" maxlength="72" id="onclick" placeholder="functionToCall" value="<?php echo $path ? $path->onclick : ''; ?>">
            </div>
            <div class="grid-7-tablet">
              <label for="classname">Class name(s) (CSS)</label>
              <input type="text" name="classname" maxlength="72" id="classname" placeholder="class1 class2" value="<?php echo $path ? $path->classname : ''; ?>">
            </div>
            <div class="grid-12-tablet">
              <label for="description"><i class="fas fa-asterisk fa-sm fa-border"></i> Description</label>
              <textarea name="description" required maxlength="256" minlegth="5" class="autosize" id="description" placeholder="Path description"><?php echo $path ? $path->description : ''; ?></textarea>
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
    $('textarea.autosize').autosize();
  })();
</script>
