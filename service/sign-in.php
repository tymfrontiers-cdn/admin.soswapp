<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
require_once APP_ROOT . "/src/Helper.php";
$gen = new Generic;
$data = new Data;
$params = $gen->requestParam([
  "rdt" => ["rdt","url"]
],'get',[]);
if ($session->isLoggedIn()) {
  $rdt = empty($params["rdt"])
    ? WHOST . "/dashboard"
    : $params["rdt"];
  HTTP\Header::redirect($rdt);
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="<?php echo WHOST; ?>/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>Admin Login | <?php echo PRJ_TITLE; ?></title>
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
    <!-- optional plugin -->
    <link rel="stylesheet" href="/app/soswapp/plugin.soswapp/css/plugin.min.css">
    <link rel="stylesheet" href="/app/soswapp/dnav.soswapp/css/dnav.min.css">
    <link rel="stylesheet" href="/app/soswapp/faderbox.soswapp/css/faderbox.min.css">
    <!-- Project styling -->
    <link rel="stylesheet" href="<?php echo \html_style("base.css"); ?>">
  </head>
  <body>
    <?php \setup_page("user-signin", "user", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <div class="view-space">
        <div class="grid-7-tablet grid-5-laptop center-tablet">
          <div class="sec-div color blue bg-white drop-shadow">
            <header class="padding -p20 border -bmedium -bbottom">
              <span class="fa-stack fa-2x push-right color-text">
                <i class="far fa-stack-2x fa-circle"></i>
                <i class="fas fa-stack-1x fa-sign-in-alt"></i>
              </span>
              <h1>Sign in to continue</h1>
            </header>
            <div class="padding -p20">
              <form
                id="long-form"
                class="block-ui"
                method="post"
                action="/app/tymfrontiers-cdn/admin.soswapp/src/SignIn.php"
                data-validate="false"
                onsubmit="sos.form.submit(this, DoSignIn); return false;"
              >
              <input type="hidden" name="rdt" value="<?php echo !empty($params['rdt']) ? $params['rdt'] : ''; ?>">
                <input type="hidden" name="reference" value="<?php echo $reference; ?>">
                <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken('long-form'); ?>">
                <input type="hidden" name="form" value="long-form">

                <div class="grid-12-tablet">
                  <label for="email"><i class="fas fa-asterisk fa-border fa-sm"></i> Email address</label>
                  <input type="email" name="email" autocomplete="email" id="email" placeholder="myname@website.com" required>
                </div>
                <div class="grid-12-tablet">
                  <label for="password"><i class="fas fa-asterisk fa-border fa-sm"></i> Password</label>
                  <input type="password" name="password" autocomplete="off" placeholder="Password" required id="password">
                </div>
                <div class="grid-7-tablet">
                  <input type="checkbox" class="solid" name="remember" value="1" id="remember">
                  <label for="remember" class="bold color-text">Remember me</label>
                </div>
                <div class="grid-5-tablet">
                  <button type="submit" id="rsd-click" class="sos-btn blue"><i class="fas fa-sign-in-alt"></i> Sign in </button>
                </div>

                <br class="c-f">
              </form>
            </div>
          </div>
        </div>
        <br class="c-f">
      </div>
    </section>
    <?php include PRJ_INC_FOOTER; ?>
    <!-- Required scripts -->
    <script src="/app/soswapp/jquery.soswapp/js/jquery.min.js">  </script>
    <script src="/app/soswapp/js-generic.soswapp/js/js-generic.min.js">  </script>
    <script src="/app/soswapp/theme.soswapp/js/theme.min.js"></script>
    <!-- optional plugins -->
    <script src="/app/soswapp/plugin.soswapp/js/plugin.min.js"></script>
    <script src="/app/soswapp/dnav.soswapp/js/dnav.min.js"></script>
    <script src="/app/soswapp/faderbox.soswapp/js/faderbox.min.js"></script>
    <!-- project scripts -->
    <script src="<?php echo \html_script ("base.min.js"); ?>"></script>
    <script src="/app/tymfrontiers-cdn/admin.soswapp/js/admin.min.js"></script>
    <script type="text/javascript">
      $("#res-cnt-view").hide();
    </script>
  </body>
</html>
