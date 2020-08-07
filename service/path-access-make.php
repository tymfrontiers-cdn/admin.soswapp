<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
\require_login(false);
\check_access("/path-access", false, "project-admin");

$errors = [];
$gen = new Generic;
$access = [];
$domain = false;
$required = ['user','domain'];
$pre_params = [
  "domain"          => ["domain","username",3,98,[],'LOWER',['-','.']],
  "user"          => ["user","username",3,12],
  "callback"      => ["callback","username",3,35,[],'MIXED']
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
  if (!(new MultiForm(MYSQL_ADMIN_DB,'user','_id'))->findBySql("SELECT * FROM :db:.:tbl: WHERE _id='{$db->escapeValue($params['user'])}' AND status IN ('ACTIVE', 'PENDING') LIMIT 1")) $errors[] = "No active [user] was found as '{$params['user']}'";
  if( !empty($params['user']) && !empty($params['domain']) ){
    // find previous access for domain
    $found = (new MultiForm(MYSQL_ADMIN_DB,'path_access','id'))
      ->findBySql("SELECT pa.id,pa.user,pa.path_name,
                          wp.domain
                   FROM :db:.:tbl: AS pa
                   LEFT JOIN :db:.work_path AS wp ON wp.name = pa.path_name
                   WHERE pa.user='{$db->escapeValue($params['user'])}'
                   AND pa.path_name IN (
                     SELECT name
                     FROM :db:.work_path
                     WHERE domain = '{$db->escapeValue($params['domain'])}'
                   )");
   if ($found) {
     foreach ($found as $fo) {
       $access[] = $fo->path_name;
     }
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
          <h1> <i class="fas fa-slash fa-universal-access"></i> Path access</h1>
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
            id="path-access-form"
            class="block-ui"
            method="post"
            action="/app/tymfrontiers-cdn/admin.soswapp/src/MakePathAccess.php"
            data-validate="false"
            onsubmit="sos.form.submit(this,doSave);return false;"
            >
            <input type="hidden" name="user" value="<?php echo @ $params['user']; ?>">
            <input type="hidden" name="domain" value="<?php echo @ $params['domain']; ?>">
            <input type="hidden" name="form" value="path-access-form">
            <input type="hidden" name="CSRF_token" value="<?php echo ($session->createCSRFtoken("path-access-form"));?>">

            <h4 class="margin -mnone align-c">Editing <?php echo "@[{$params['user']}]'s" ?> work/path access for domain.</h4>
            <h3 class="align-c"><?php echo @ $params['domain']; ?></h3>

            <div class="grid-12-tablet">
              <h3>Paths</h3>
            <?php
              $path_type = [
                "READ" => [],
                "ALTER" => []
              ];
              if ($paths = (new MultiForm(MYSQL_ADMIN_DB,'work_path','name'))
                ->findBySql("SELECT wp.name,wp.domain,wp.path, wp.type, wp.`access_rank`, wp.title,wp.icon,wp.description

                             FROM :db:.:tbl: AS wp
                             WHERE wp.domain='{$db->escapeValue($params['domain'])}'
                             AND wp.`access_rank` <= (
                               SELECT `rank`
                               FROM :db:.work_group
                               WHERE name = (
                                 SELECT work_group
                                 FROM :db:.user
                                 WHERE _id ='{$db->escapeValue($params['user'])}'
                                 LIMIT 1
                               )
                               LIMIT 1
                             )")
              ) {
                foreach($paths as $pt) {
                  $path_type[$pt->type][] = $pt;
                }
                echo " <h3>Alter Access</h3>";
                echo "<p> <i class=\"fas fa-exclamation-triangle\"></i> Can create, change and delete.</p>";
                foreach($path_type["ALTER"] as $path) {
                  echo " <input title=\"{$path->description}\" name='path' type='checkbox' id=\"path-{$path->name}\" value=\"{$path->name}\" ";
                  echo $access && \in_array($path->name,$access) ? 'checked' : '';
                  echo ">";
                  echo " <label for=\"path-{$path->name}\" title=\"{$path->description}\">{$path->title} ({$path->path})</label>";
                  echo " <br>";
                }
                echo " <h3>Read Access</h3>";
                foreach($path_type["READ"] as $path) {
                  echo " <input title=\"{$path->description}\" name='path' type='checkbox' id=\"path-{$path->name}\" value=\"{$path->name}\" ";
                  echo $access && \in_array($path->name,$access) ? 'checked' : '';
                  echo ">";
                  echo " <label for=\"path-{$path->name}\" title=\"{$path->description}\">{$path->title} ({$path->path})</label>";
                  echo " <br>";
                }
              } ?>
            </div>

            <div class="grid-3-tablet">
              <br>
              <button id="submit-form" type="submit" class="sos-btn blue"> <i class="fas fa-save"></i> Save </button>
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
  })();
</script>
