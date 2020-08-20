<?php
namespace TymFrontiers;

require_once "../.appinit.php";
require_once APP_BASE_INC;
require_once APP_ROOT . "/src/Helper.php";

\require_login(true);
\check_access("/path-accesses", true, "project-admin");
$gen = new Generic;
$params = $gen->requestParam([
  "domain"          => ["domain","username",3,98,[],'LOWER',['-','.']],
  "user"          => ["user","username",3,12,[], "MIXED"],
],$_GET,[]);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="<?php echo WHOST; ?>/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>Path Access | <?php echo PRJ_TITLE; ?></title>
    <?php include PRJ_INC_ICONSET; ?>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
    <meta name="author" content="<?php echo PRJ_AUTHOR; ?>">
    <meta name="creator" content="<?php echo PRJ_CREATOR; ?>">
    <meta name="publisher" content="<?php echo PRJ_PUBLISHER; ?>">
    <meta name="robots" content='nofollow'>
    <!-- Theming styles -->
    <link rel="stylesheet" href="/app/soswapp/font-awesome.soswapp/css/font-awesome.min.css">
    <link rel="stylesheet" href="/app/soswapp/theme.soswapp/css/theme.min.css">
    <link rel="stylesheet" href="/app/soswapp/theme.soswapp/css/theme-<?php echo PRJ_THEME; ?>.min.css">
    <link rel="stylesheet" href="/app/soswapp/fancybox.soswapp/css/fancybox.min.css">
    <link rel="stylesheet" href="/app/soswapp/jcrop.soswapp/css/jcrop.min.css">
    <!-- optional plugin -->
    <link rel="stylesheet" href="/app/soswapp/plugin.soswapp/css/plugin.min.css">
    <link rel="stylesheet" href="/app/soswapp/dnav.soswapp/css/dnav.min.css">
    <link rel="stylesheet" href="/app/soswapp/faderbox.soswapp/css/faderbox.min.css">
    <!-- Project styling -->
    <link rel="stylesheet" href="<?php echo \html_style("base.min.css"); ?>">
  </head>
  <body>
    <?php \setup_page("/app/admin/path-accesses", "project-admin", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <form
        id="delete-form"
        method="post"
        action="/app/tymfrontiers-cdn/admin.soswapp/src/DeletePathAccess.php"
        data-validate="false"
        onsubmit="sos.form.submit(this,checkDelete);return false;"
      >
        <input type="hidden" name="form" value="pathaccess-delete-form">
        <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("pathaccess-delete-form");?>">
        <input type="hidden" name="id" value="">
      </form>
      <div class="view-space">
        <br class="c-f">
          <div class="grid-8-tablet center-tablet">
            <form
              id="query-form"
              class="block-ui color blue"
              method="post"
              action="/app/tymfrontiers-cdn/admin.soswapp/src/FetchPathAccess.php"
              data-validate="false"
              onsubmit="sos.form.submit(this,doFetch);return false;"
              >
              <input type="hidden" name="form" value="domain-query-form">
              <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("domain-query-form");?>">

              <div class="grid-6-tablet">
                <h4 class="margin -mnone">* Work Domain</h4>
                <select name="domain" required onchange="if($(this).val().length > 0){$('#floatn-plus').data('domain',$(this).val());}">
                <?php if ($domains = (new MultiForm(MYSQL_ADMIN_DB,'work_domain','name'))->findAll()) {
                  foreach ($domains as $domain) {
                    echo " <option value=\"{$domain->name}\" title=\"{$domain->info}\" ";
                    echo !empty($params['domain']) && $params['domain'] == $domain->name ? 'selected' : '';
                    echo ">{$domain->name}</option>";
                  }
                } ?>
                </select>
              </div>

              <div class="grid-4-tablet">
                <label for="user"> <i class="fas fa-user"></i> User (Admin ID)</label>
                <input
                  type="text"
                  name="user"
                  value="<?php echo !empty($params['user']) ? $params['user'] : $session->name; ?>"
                  id="user"
                  placeholder="ADMIN"
                  onmouseout="$('#floatn-plus').data('user',$(this).val());"
                  onkeyup="$('#floatn-plus').data('user',$(this).val());"
                >
              </div>
              <div class="grid-4-phone grid-2-tablet">
                <label>&nbsp;</label> <br>
                <button type="submit" class="sos-btn blue"> <i class="fas fa-search"></i></button>
              </div>

              <br class="c-f">
            </form>
            <p class="align-c padding -p10">
              <a href="../admin/users" class="blue"> <i class="fas fa-link"></i> Browse users</a>
            </p>
          </div>

          <div class="sec-div padding -p10">
            <h2>Paths accessible</h2>
            <table class="vertical color blue padding -pnone  ff-open-sans">
              <thead class="color-bg align-l">
                <tr>
                  <th>User</th>
                  <th>Domain</th>
                  <th>Path</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="record-list"></tbody>
            </table>
            <br class="c-f">
          </div>


        <br class="c-f">
      </div>
    </section>

    <button
      type="button"
      data-callback="refreshList"
      data-domain="<?php echo !empty($params['domain']) ? $params['domain'] : (@ $domains[0]->name); ?>"
      data-user="<?php echo !empty($params['user']) ? $params['user'] : $session->name; ?>"
      onclick="sos.faderBox.url('/app/tymfrontiers-cdn/admin.soswapp/service/path-access-make.php', $(this).data(), {exitBtn: true});"
      class="sos-btn blue"
      id="floatn-plus"> <i class="fas fa-edit"></i></button>
    <?php include PRJ_INC_FOOTER; ?>
    <!-- Required scripts -->
    <script src="/app/soswapp/jquery.soswapp/js/jquery.min.js">  </script>
    <script src="/app/soswapp/js-generic.soswapp/js/js-generic.min.js">  </script>
    <script src="/app/soswapp/fancybox.soswapp/js/fancybox.min.js">  </script>
    <script src="/app/soswapp/jcrop.soswapp/js/jcrop.min.js">  </script>
    <script src="/app/soswapp/theme.soswapp/js/theme.min.js"></script>
    <!-- optional plugins -->
    <script src="/app/soswapp/plugin.soswapp/js/plugin.min.js"></script>
    <script src="/app/soswapp/dnav.soswapp/js/dnav.min.js"></script>
    <script src="/app/soswapp/faderbox.soswapp/js/faderbox.min.js"></script>
    <!-- project scripts -->
    <script src="<?php echo \html_script ("base.min.js"); ?>"></script>
    <script src="/app/tymfrontiers-cdn/admin.soswapp/js/admin.min.js"></script>
    <script type="text/javascript">
      var param = $("#param").data();
      function refreshList(){  $('#query-form').submit(); }
      function doFetch(data){
        if( data && data.errors < 1 && data.access.length > 0){
          $('#record-list').listData( data.access );
          removeAlert();
        }else{
          $('#record-list').html('');
        }
      }
      $.fn.listData = function(obj){
        var html = "";
        $.each(obj, function(i, el) {
          html += "<tr>";
            html += ( "<td>" +el.user_name+ " ("+el.user+")</td>" );
            html += ( "<td>" +el.domain+ "</td>" );
            html += ( "<td>" +el.title+ " ("+el.path+")</td>" );

            html += "<td>";
              html += (" <a href=\"javascript:void(0)\" class='red' onclick=\"doDelete('"+el.id+"')\"> <i class=\"fas fa-trash\"></i> Delete </a>");
            html+= "</td>";
          html += "</tr>";
        });
        $(this).html(html);
      }
      function doDelete(id){
        if( confirm("Are you sure you want to delete this path/access?") ){
          $("#delete-form input[name=id]").val(id);
          $('#delete-form').submit();
        }
      }
      function checkDelete(data){
        if( data && data.status == "0.0"){
          setTimeout(function(){
            removeAlert();
            refreshList();
          },1800);
        }
      }
      $(document).ready(function() {
        if ( $('select[name=domain]').val().length > 0 && $('input[name=user]').val().length > 0 ) {
          refreshList();
        }
      });

    </script>
  </body>
</html>
