<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
\require_login(false);
\check_access("/users", false, "project-admin");

$errors = [];
$gen = new Generic;
$data_obj = new Data;
$tym = new BetaTym;
$required = ['id'];
$pre_params = [
  "id" => ["id","username",3,12],
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
if (!empty($params['id'])) {
  $user = new \SOS\Admin($params['id']);
  if (empty($user->id())) $errors[] = "No user account found!";
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
          <h1 class="fw-lighter"> <i class="fas fa-info-circle"></i> User info</h1>
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
            <p class="align-c">
              <img title="<?php echo "{$user->name} {$user->surname}"; ?>" style="width:220px; height:auto" alt="avatar" src="<?php echo $user->avatar; ?>">
            </p>
            <table class="horizontal">
              <tr>
                <th>Region</th>
                <td><?php echo "{$user->state}/{$user->country}"; ?></td>
              </tr>
              <tr>
                <th>Account ID/Status</th>
                <td><?php echo "<b>{$user->id()}</b> /{$user->status}"; ?></td>
              </tr>
              <tr>
                <th>Name</th>
                <td><?php echo $user->name; ?></td>
              </tr>
              <tr>
                <th>Surname</th>
                <td><?php echo $user->surname; ?></td>
              </tr>
              <tr>
                <th>Email</th>
                <td><a class="blue" href="mailto:<?php echo $user->email; ?>"><?php echo $user->email; ?></a></td>
              </tr>
              <tr>
                <th>Phone</th>
                <td><a class="blue" href="tel:<?php echo $user->phone; ?>"><?php echo !empty($user->phone) ? $data_obj->phoneToLocal($user->phone) : ""; ?></a></td>
              </tr>
              <tr>
                <th>Work group</th>
                <td><?php echo $user->work_group; ?></td>
              </tr>
            </table> <br>
            <hr>
            <p>
              <b>Added by:</b> <a href="#" onclick="sos.faderBox.url('/app/tymfrontiers-cdn/admin.soswapp/service/user-view.php',{id:'<?php echo $user->id(); ?>'},{exitBtn:true});"><?php echo "{$user->author_name} - {$user->author()}"; ?></a> <br>
              <b>On</b> <?php echo $tym->dateTym($user->created()); ?>
            </p>
        <?php } ?>
      </div>
    </div>
  </div>
  <br class="c-f">
</div>
</div>

<script type="text/javascript">
  var param = $('#rparam').data();
  (function(){
  })();
</script>
