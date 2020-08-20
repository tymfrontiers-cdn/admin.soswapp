<?php
namespace TymFrontiers;

require_once "../.appinit.php";
require_once APP_BASE_INC;
require_once APP_ROOT . "/src/Helper.php";

\require_login(true);
\check_access("/users", true, "project-admin");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="<?php echo WHOST; ?>/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>Admin accounts | <?php echo PRJ_TITLE; ?></title>
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
    <?php \setup_page("/app/admin/users", "project-admin", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <form
      id="alter-form"
      method="post"
      action="/app/tymfrontiers-cdn/admin.soswapp/src/AlterUser.php"
      data-validate="false"
      onsubmit="sos.form.submit(this,checkMake);return false;"
      >
      <input type="hidden" name="form" value="user-alter-form">
      <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("user-alter-form");?>">
      <input type="hidden" name="id" value="">
      <input type="hidden" name="status" value="">
    </form>
      <div class="view-space">
        <br class="c-f">
          <div class="grid-8-tablet center-tablet">
            <form
              id="query-form"
              class="block-ui color blue"
              method="post"
              action="/app/tymfrontiers-cdn/admin.soswapp/src/FetchUser.php"
              data-validate="false"
              onsubmit="sos.form.submit(this,doFetch);return false;"
              >
              <input type="hidden" name="form" value="user-query-form">
              <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("user-query-form");?>">

              <div class="grid-6-tablet">
                <label for="search"> <i class="fas fa-search"></i> Search</label>
                <input type="search" name="search" value="<?php echo !empty($_GET['search']) ? $_GET['search'] :''; ?>" id="search" placeholder="Keyword search">
              </div>
              <div class="grid-4-phone grid-2-tablet">
                <label for="page"> <i class="fas fa-file-alt"></i> Page</label>
                <input type="number" name="page" id="page" placeholder="1" value="1">
              </div>
              <div class="grid-4-phone grid-2-tablet">
                <label for="limit"> <i class="fas fa-sort-numeric-up"></i> Limit</label>
                <input type="number" name="limit" id="limit" placeholder="25" value="25">
              </div>
              <div class="grid-4-phone grid-2-tablet"> <br>
                <button type="submit" class="btn blue"> <i class="fas fa-search"></i></button>
              </div>
              <br class="c-f">
            </form>
            <p class="align-c">
              <b>Records:</b> <span id="records">00</span> |
              <b>Pages:</b> <span id="pages">00</span>
            </p>
          </div>

          <div class="sec-div padding -p10">
            <h2>Admin users</h2>
            <table class="vertical color blue padding -pnone ff-open-sans">
              <thead class="color-bg align-l">
                <tr>
                  <th>Status</th>
                  <th>Name/ID</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="record-list"></tbody>
            </table>
            <div class="sec-div padding -p5">
              <div class="grid-3-phone grid-1-tablet push-right padding -pnone">
                <button onclick="$(this).page()" type="button" id="next-page" style="width:100%" data-page="0" class="btn blue no-shadow no-radius"> <i class="fas fa-angle-right fa-lg"></i></button>
              </div>
              <div class="grid-3-phone grid-1-tablet push-right padding -pnone">
                <button onclick="$(this).page()" type="button" id="previous-page" style="width:100%" data-page="0" class="btn blue no-shadow no-radius"> <i class="fas fa-angle-left fa-lg"></i></button>
              </div>
            </div>
            <br class="c-f">
          </div>


        <br class="c-f">
      </div>
    </section>
    <button type="button" onclick="sos.faderBox.url('/app/tymfrontiers-cdn/admin.soswapp/service/user-invite.php', {}, {exitBtn: true});" class="sos-btn blue" id="floatn-plus"> <i class="fas fa-plus"></i></button>
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
        if( data && data.status == "0.0" && data.users.length > 0){
          $('#pages').text(data.pages);
          $('#records').text(data.records);
          $('#page').val(data.page);
          $('#limit').val(data.limit);
          if( data.has_next_page ) $('#next-page').data('page',data.next_page);
          if( data.has_previous_page ) $('#previous-page').data('page',data.previous_page);
          $('#record-list').listData( data.users );
          removeAlert();
        }else{
          $('#record-list').html('');
        }
      };
      $.fn.listData = function(obj){
        var html = "";
        $.each(obj, function(i, el) {
          html += "<tr title=\"Added by: "+el.author_name+" - "+el.author+" | On: "+el.created+"\">";
            html += ( "<td>" +el.work_group+ " ("+el.status+")</td>" );
            html += "<td>";
              html += ("<a class='blue' href='#' onclick=\"sos.faderBox.url('/app/tymfrontiers-cdn/admin.soswapp/service/user-view.php',{id:'"+el.id+"'},{exitBtn:true})\"> <i class=\"fas fa-window-maximize\"></i> ");
                html += ( el.name + " @<i>"+el.id+"</i>");
              html += "</a>";
            html += "</td>";
            html += ( "<td>" +el.email+ "</td>" );
            html += ( "<td>" +el.phone_local+ "</td>" );

            html += "<td class='form block-ui'> <select class=\"alter-prop\">";
              html += "<option value=''> Alter user</option>";
              if ( in_array(el.status, ["PENDING", "ACTIVE"])) {
                html += ("<option value='SUSPENDED' data-id='"+el.id+"' data-status='SUSPENDED'> Suspend</option>");
                html += ("<option value='BANNED' data-id='"+el.id+"' data-status='BANNED'> Ban</option>");
                html += ("<option value='DISABLED' data-id='"+el.id+"' data-status='DISABLED'> Disable</option>");
              } if (in_array(el.status, ['SUSPENDED'])) {
                html += ("<option value='ACTIVE' data-id='"+el.id+"' data-status='ACTIVE'> Activate</option>");
              } if (in_array(el.status,['ACTIVE','PENDING'])) {
                html += ("<option value='give-access' data-id='"+el.id+"'> Give access</option>");
              }
          html += "</tr>";
        });
        $(this).html(html);
      };
      function checkMake(data){
        if( data && data.status == "0.0"){
          setTimeout(function(){
            removeAlert();
            refreshList();
          },1800);
        }
      }
      $(document).ready(function() {
        refreshList();
        $(document).on("change",".alter-prop", function(){
          var sel = $(this);
          var prop = $(this).find("option:selected").data();
          if ($(this).val() !== '' && $(this).val() !=='give-access') {
            if (confirm("Do you want to change this user's status?")) {
              $('#alter-form input[name=id]').val(prop.id);
              $('#alter-form input[name=status]').val(prop.status);
              $('#alter-form').submit();
            } else {
              sel.val("");
            }
          } else if ($(this).val() == 'give-access') {
            window.location = "/app/admin/path-accesses?user=" + prop.id;
          } else {
            //
          }
        });
      });

    </script>
  </body>
</html>
