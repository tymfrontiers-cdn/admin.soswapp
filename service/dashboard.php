<?php
namespace TymFrontiers;

require_once "../.appinit.php";
require_once APP_BASE_INC;
require_once APP_ROOT . "/src/Helper.php";

\require_login(true);
\check_access("/dashboard", true, "project-admin");

$navs = [];
$query = "SELECT wp.name, wp.path, wp.access_rank, wp.access_rank_strict, wp.title,
                 wp.icon, wp.description,
                 dm.path AS domain_path
           FROM :db:.:tbl: AS wp
           LEFT JOIN :db:.work_domain AS dm ON dm.name = wp.domain
           WHERE wp.nav_visible = TRUE
           AND wp.domain='project-admin'
           AND  (
             (wp.access_rank_strict = TRUE AND wp.access_rank = {$session->access_rank}) OR (
               wp.access_rank <= {$session->access_rank}
             )
           )
           AND (
             wp.name IN(
               SELECT path_name
               FROM :db:.path_access
               WHERE user='{$db->escapeValue($session->name)}'
             ) OR (
               (
                 SELECT COUNT(*)
                 FROM :db:.path_access
                 WHERE path_name = (
                    SELECT `name`
                    FROM :db:.work_path
                    WHERE `domain` = 'project-admin'
                    AND `path` = '/'
                    LIMIT 1
                 )
                 AND user = '{$db->escapeValue($session->name)}'
               ) > 0
             )
           )
           ORDER BY wp.`sort`, wp.title ASC";
$found_nav = (new \TymFrontiers\MultiForm(MYSQL_ADMIN_DB,'work_path','name'))->findBySql($query);
// echo $database->last_query;
// exit;
if ($found_nav) {
  foreach ($found_nav as $nav) {
    $navs[] = [
      "access_rank" => (int)$nav->access_rank,
      "access_rank_strict" => (int)$nav->access_rank_strict,
      "title" => $nav->title,
      "path" => $nav->path,
      "link" => WHOST . $nav->domain_path . $nav->path,
      "onclick" => (!empty($nav->onclick) ? $nav->onclick : ''),
      "icon" => (!empty($nav->icon) ? \html_entity_decode($nav->icon) : ""),
      "name" => $nav->name,
      "classname" => (!empty($nav->classname) ? $nav->classname : '')
    ];
  }
}
// echo "<tt> <pre>";
// \print_r($navs);
// echo "</pre></tt>";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="<?php echo WHOST; ?>/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>Administration | <?php echo PRJ_TITLE; ?></title>
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
    <link rel="stylesheet" href="/app/tymfrontiers-cdn/admin.soswapp/css/admin.min.css">
  </head>
  <body>
    <?php \setup_page("/admin/dashboard", "project-admin", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <div class="view-space">
        <br class="c-f">
        <div class="grid-10-tablet grid-8-desktop center-tablet">
          <!-- dashlist -->
          <ul class="dash-list">
            <?php if ($navs): foreach($navs as $dash){ if($dash['link'] !== THIS_PAGE){ ?>
              <li> <a <?php echo (new \TymFrontiers\Validator)->url($dash['link'],['link','url'])
              ? "href=\"{$dash['link']}\""
              : "href='javascript:void(0)' onclick=\"{$dash['link']}()\"" ?>>
              <span class="fa-stack fa-3x">
                <i class="fas fa-circle fa-stack-2x"></i>
                <?php echo \str_replace("fas fa-", "fas fa-stack-1x fa-inverse fa-",$dash['icon']); ?>
              </span>
              <h3><?php echo $dash['title']; ?></h3>
            </a></li>
          <?php } } endif; ?>
        </ul>
        </div>
        <br class="c-f">
      </div>
    </section>
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
    <script src="/app/tymfrontiers-cdn/admin.soswapp/css/admin.min.css"></script>
  </body>
</html>
