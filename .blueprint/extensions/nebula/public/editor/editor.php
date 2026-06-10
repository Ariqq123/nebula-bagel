<?php
  require __DIR__ . '/../../../../../vendor/autoload.php';
  $app = require_once __DIR__.'/../../../../../bootstrap/app.php';
  $app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::capture());

  use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Client\BlueprintClientLibrary as BlueprintExtensionLibrary;
  $settings = app()->make('Pterodactyl\Contracts\Repository\SettingsRepositoryInterface');
  $blueprint = app()->make(BlueprintExtensionLibrary::class, ['settings' => $settings]);

  $userId = Auth::id(); // fetch authenticated user's ID
  $user = Auth::user(); // fetch authenticated user
  if($user == false) { echo('401 Unauthorized'); return; }
  if($user->root_admin != 1) { echo('403 Forbidden'); return; }
?>
<head>
  <link rel="stylesheet" href="./assets/base.css">
  <link rel="stylesheet" href="./assets/css/modes/tinypreview.css">
  <script src="./assets/js/navigation.js"></script>
  <script src="./assets/js/editor.js"></script>
  <!-- popperjs --> <script src="https://unpkg.com/@popperjs/core@2"></script>
  <!-- tippy.js --> <script src="https://unpkg.com/tippy.js@6"></script>
  <!-- tippy.js --> <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/animations/shift-away.css">
  <style>.match-dashboard-bg {background-color: <?php echo $blueprint->dbGet("nebula", "palette_dashboard_7"); ?> !important;} .match-auth-bg {background-color: <?php echo $blueprint->dbGet("nebula", "palette_auth_1"); ?> !important;}</style>
  <title>Nebula Designer</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="/extensions/nebula/editor/assets/favicon.ico">
  <?php if($blueprint->dbGet("nebula", "plausible_tracking") == 1) { echo('<script defer="" data-domain="demo.nebula.style" src="https://plausible.prpl.wtf/js/script.js"></script>'); } ?>
</head>

<html style="background-color: #050404">
  <body>
    <div class="container">
      <div class="navigation">
        <button onclick="navigationAction('admin')" to="admin" class="return-button"><i class="bi bi-arrow-90deg-left"></i></button>
        <div style="height: 80px;"></div>
        <button onclick="navigationAction('general')" to="general" class="navigation-button active"><i class="bi bi-sliders2"></i></button>
        <button onclick="navigationAction('palette')" to="palette" class="navigation-button"><i class="bi bi-palette2"></i></button>
        <button onclick="navigationAction('sidebar')" to="sidebar" class="navigation-button"><i class="bi bi-layout-sidebar-inset"></i></button>
        <button onclick="navigationAction('dashboard')" to="dashboard" class="navigation-button"><i class="bi bi-grid-1x2"></i></button>
        <button onclick="navigationAction('authentication')" to="authentication" class="navigation-button"><i class="bi bi-box-arrow-in-right"></i></button>
        <button onclick="navigationAction('more')" to="more" class="navigation-button"><i class="bi bi-nut"></i></button>
        <div class="save-padding"></div>
        <button onclick="saveAction()" class="save-button"><i class="bi bi-floppy-fill"></i></button>
      </div>

      <div class="editor fade">
        <form action="/admin/extensions/nebula" method="POST" id="editor-form" autocomplete="off">
          <div class="editor-container">
            <h2 class="editor-title">General</h2>
            <p class="editor-description">Configure general configuration options.</p>

            <!-- Website links -->
            <div class="option">
              <button class="modal-open" onclick="modal('#weblinks-modal')" type="button" style="float: left"><i class="bi bi-plus-lg"></i></button>
              <p class="option-title with-button">Website links</p>
              <!-- Enabled -->
              <input type="radio" id="websitelinks-on" name="website_links" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "website_links") == "1") { echo("checked=''"); } ?>>
              <label for="websitelinks-on" class="option-radio" onclick="event.preventDefault();modal('#weblinks-modal');">
                <img src="./assets/images/general/websitelinks/on.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="websitelinks-off" name="website_links" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "website_links") == "0") { echo("checked=''"); } ?>>
              <label for="websitelinks-off" class="option-radio" onclick="clearWeblinks()">
                <img src="./assets/images/general/websitelinks/off.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Link buttons to external pages on your Pterodactyl panel.</p>
            </div>

            <!-- Alert -->
            <div class="option">
              <button class="modal-open" onclick="modal('#alert-modal')" type="button" style="float: left"><i class="bi bi-plus-lg"></i></button>
              <p class="option-title with-button">Alert</p>
              <!-- Enabled -->
              <input type="radio" id="alert-on" name="alert" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "alert") == "1") { echo("checked=''"); } ?>>
              <label for="alert-on" class="option-radio" onclick="event.preventDefault();modal('#alert-modal');">
                <img src="./assets/images/general/alert/on.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="alert-off" name="alert" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "alert") == "0") { echo("checked=''"); } ?>>
              <label for="alert-off" class="option-radio">
                <img src="./assets/images/general/alert/off.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Display a custom, configurable alert/announcement on your dashboard.</p>
            </div>
          </div>

          <!-- Weblinks modal -->
          <div class="editor-modal" id="weblinks-modal">
            <button class="modal-close" onclick="closeModal('#weblinks-modal')" type="button" style="float: left"><i class="bi bi-chevron-left"></i></button>
            <h2 class="editor-title with-button">Website links</h2>
            <p class="editor-description">Link buttons to external pages on both your authentication page and Pterodactyl panel which are visible to tablet and desktop users.</p>

            <!-- Websites -->
            <div class="option">
              <p class="option-title">Websites</p>
              <!-- Support Page -->
              <div class="option-container with-margin">
                <span class="option-icon"><i class="bi bi-life-preserver"></i></span>
                <input type="text" id="weblink-support" name="weblink_support" class="option-input with-icon" placeholder="https://support.nebula.style" value="<?php echo $blueprint->dbGet("nebula", "weblink_support"); ?>">
                <script> tippy('.option-container:has(.option-icon + #weblink-support)', { content: "Support Page", arrow: false, animation: 'shift-away' }); </script>
              </div>
              <!-- Billing Panel -->
              <div class="option-container with-margin">
                <span class="option-icon"><i class="bi bi-cash-coin"></i></span>
                <input type="text" id="weblink-billing" name="weblink_billing" class="option-input with-icon" placeholder="https://billing.nebula.style" value="<?php echo $blueprint->dbGet("nebula", "weblink_billing"); ?>">
                <script> tippy('.option-container:has(.option-icon + #weblink-billing)', { content: "Billing Panel", arrow: false, animation: 'shift-away' }); </script>
              </div>
              <!-- Status Page -->
              <div class="option-container with-margin">
                <span class="option-icon"><i class="bi bi-bar-chart-fill"></i></span>
                <input type="text" id="weblink-status" name="weblink_status" class="option-input with-icon" placeholder="https://status.nebula.style" value="<?php echo $blueprint->dbGet("nebula", "weblink_status"); ?>">
                <script> tippy('.option-container:has(.option-icon + #weblink-status)', { content: "Status Page", arrow: false, animation: 'shift-away' }); </script>
              </div>
              <p class="option-footer">Links to websites related to your Pterodactyl panel.</p>
            </div>

            <!-- Social links -->
            <div class="option">
              <p class="option-title">Social links</p>
              <!-- Discord Guild -->
              <div class="option-container with-margin">
                <span class="option-icon"><i class="bi bi-discord"></i></span>
                <input type="text" id="weblink-social-discord" name="weblink_social_discord" class="option-input with-icon" placeholder="CUwHwv6xRe" value="<?php echo $blueprint->dbGet("nebula", "weblink_social_discord"); ?>">
                <script> tippy('.option-container:has(.option-icon + #weblink-social-discord)', { content: "Discord Guild", arrow: false, animation: 'shift-away' }); </script>
              </div>
              <!-- GitHub Profile/Repository -->
              <div class="option-container with-margin">
                <span class="option-icon"><i class="bi bi-github"></i></span>
                <input type="text" id="weblink-social-github" name="weblink_social_github" class="option-input with-icon" placeholder="prplwtf" value="<?php echo $blueprint->dbGet("nebula", "weblink_social_github"); ?>">
                <script> tippy('.option-container:has(.option-icon + #weblink-social-github)', { content: "Github Profile/Repository", arrow: false, animation: 'shift-away' }); </script>
              </div>
              <p class="option-footer">Invite links or usernames to various social platforms.</p>
            </div>

            <!-- Alignment -->
            <div class="option">
              <p class="option-title">Alignment</p>
              <!-- Left -->
              <input type="radio" id="linkalign-left" name="website_links_align" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "website_links_align") == "0") { echo("checked=''"); } ?>>
              <label for="linkalign-left" class="option-radio sm aspect-square">
                <img src="./assets/images/general/linkalign/left.png"/>
              </label>
              <!-- Right -->
              <input type="radio" id="linkalign-right" name="website_links_align" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "website_links_align") == "1") { echo("checked=''"); } ?>>
              <label for="linkalign-right" class="option-radio sm aspect-square">
                <img src="./assets/images/general/linkalign/right.png"/>
              </label>
              <p class="option-footer">Choose where to align the website links. Social links are aligned in opposite of website links.</p>
            </div>
          </div>

          <!-- Alert modal -->
          <div class="editor-modal" id="alert-modal">
            <button class="modal-close" onclick="closeModal('#alert-modal')" type="button" style="float: left"><i class="bi bi-chevron-left"></i></button>
            <h2 class="editor-title with-button">Alert</h2>
            <p class="editor-description">Display a custom, configurable alert/announcement on your dashboard.</p>

            <!-- Content -->
            <div class="option">
              <p class="option-title">Content</p>
              <textarea id="alertcontent" name="alert_text" class="option-textarea" rows="4" placeholder="Start writing something.."><?php echo $blueprint->dbGet("nebula", "alert_text") ?></textarea>
              <p class="option-footer">Make your alert yours by writing something to inform users about. <a href="https://daringfireball.net/projects/markdown/" target="_blank">Markdown</a> syntax is supported for styling.</p>
            </div>

            <!-- Icon -->
            <div class="option">
              <p class="option-title">Icon</p>
              <!-- Disabled -->
              <input type="radio" id="alerticon-disabled" name="alert_icon" value="megaphone-fill" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_icon") == "megaphone-fill") { echo("checked=''"); } ?>/>
              <!-- Megaphone -->
              <input type="radio" id="alerticon-megaphone" name="alert_icon" value="megaphone-fill" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_icon") == "megaphone-fill") { echo("checked=''"); } ?>/>
              <label for="alerticon-megaphone" class="option-radio sm aspect-square" onclick="alertDepend(event)">
                <img src="./assets/images/general/alerticon/megaphone.png"/>
              </label>
              <!-- Warning -->
              <input type="radio" id="alerticon-warning" name="alert_icon" value="exclamation-triangle-fill" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_icon") == "exclamation-triangle-fill") { echo("checked=''"); } ?>/>
              <label for="alerticon-warning" class="option-radio sm aspect-square" onclick="alertDepend(event)">
                <img src="./assets/images/general/alerticon/warning.png"/>
              </label>
              <!-- Success -->
              <input type="radio" id="alerticon-success" name="alert_icon" value="check-circle-fill" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_icon") == "check-circle-fill") { echo("checked=''"); } ?>/>
              <label for="alerticon-success" class="option-radio sm aspect-square" onclick="alertDepend(event)">
                <img src="./assets/images/general/alerticon/success.png"/>
              </label>
              <!-- Database -->
              <input type="radio" id="alerticon-database" name="alert_icon" value="database-fill" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_icon") == "database-fill") { echo("checked=''"); } ?>/>
              <label for="alerticon-database" class="option-radio sm aspect-square" onclick="alertDepend(event)">
                <img src="./assets/images/general/alerticon/database.png"/>
              </label>
              <!-- Message -->
              <input type="radio" id="alerticon-message" name="alert_icon" value="chat-square-text-fill" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_icon") == "chat-square-text-fill") { echo("checked=''"); } ?>/>
              <label for="alerticon-message" class="option-radio sm aspect-square" onclick="alertDepend(event)">
                <img src="./assets/images/general/alerticon/message.png"/>
              </label>
              <!-- Gear -->
              <input type="radio" id="alerticon-gear" name="alert_icon" value="gear-fill" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_icon") == "gear-fill") { echo("checked=''"); } ?>/>
              <label for="alerticon-gear" class="option-radio sm aspect-square" onclick="alertDepend(event)">
                <img src="./assets/images/general/alerticon/gear.png"/>
              </label>
              <!-- Rocket -->
              <input type="radio" id="alerticon-rocket" name="alert_icon" value="rocket-takeoff-fill" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_icon") == "rocket-takeoff-fill") { echo("checked=''"); } ?>/>
              <label for="alerticon-rocket" class="option-radio sm aspect-square" onclick="alertDepend(event)">
                <img src="./assets/images/general/alerticon/rocket.png"/>
              </label>
              <!-- Reception -->
              <input type="radio" id="alerticon-reception" name="alert_icon" value="reception-4" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_icon") == "reception-4") { echo("checked=''"); } ?>/>
              <label for="alerticon-reception" class="option-radio sm aspect-square" onclick="alertDepend(event)">
                <img src="./assets/images/general/alerticon/reception.png"/>
              </label>
              <p class="option-footer">Pick an icon for this alert which will show up in front of the alert message.</p>
            </div>

            <!-- Position -->
            <div class="option">
              <p class="option-title">Position</p>
              <!-- Disabled -->
              <input type="radio" id="alertposition-disabled" name="alert_position" value="sticky" class="hidden"/>
              <!-- Sticky -->
              <input type="radio" id="alertposition-sticky" name="alert_position" value="sticky" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_position") == "sticky") { echo("checked=''"); } ?>>
              <label for="alertposition-sticky" class="option-radio" onclick="alertDepend(event)">
                <img src="./assets/images/general/alertposition/sticky.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Static -->
              <input type="radio" id="alertposition-static" name="alert_position" value="static" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_position") == "static") { echo("checked=''"); } ?>>
              <label for="alertposition-static" class="option-radio" onclick="alertDepend(event)">
                <img src="./assets/images/general/alertposition/static.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Choose to either keep the alert in the visitor's viewport or at the top of the page.</p>
            </div>

            <!-- Dismissable -->
            <div class="option">
              <p class="option-title">Dismissable</p>
              <!-- Disabled -->
              <input type="radio" id="alertdismiss-disabled" name="alert_dismiss" value="0" class="hidden"/>
              <!-- Enabled -->
              <input type="radio" id="alertdismiss-on" name="alert_dismiss" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_dismiss") == "1") { echo("checked=''"); } ?>>
              <label for="alertdismiss-on" class="option-radio" onclick="alertDepend(event)">
                <img src="./assets/images/general/alertdismiss/on.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="alertdismiss-off" name="alert_dismiss" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "alert_dismiss") == "0") { echo("checked=''"); } ?>>
              <label for="alertdismiss-off" class="option-radio" onclick="alertDepend(event)">
                <img src="./assets/images/general/alertdismiss/off.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Allow or disallow users to hide/dismiss the alert.</p>
            </div>
          </div>

          <div id="editor-submit">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="_endpoint" value="/extensions/nebula/editor/editor.php">
            <input type="hidden" name="_method" value="PATCH">
            <button type="submit" class="hidden" id="submit"></button>
          </div>
        </form>
      </div>
      <div class="preview fade">
        <iframe
          title="Preview"
          class="preview-frame mobile-xs"
          src="/extensions/nebula/preview/placeholder.php"
          style="--scale: 1; --size: 100%;"
          loading="lazy">
        </iframe>
      </div>
      <div id="notif-unsaved" class="notif">
        <div class="notif-hitbox"></div>
        <div class="notif-container">
          <h2 class="notif-title">
            <i class="bi bi-exclamation-triangle-fill" style="margin-right: 4px;"></i>
            Unsaved changes!
          </h2>
          <p class="notif-text">You made changes that haven't been saved yet, do you want to save them?</p>
          <button class="notif-button notif-primary" id="unsaved-save" onclick="saveUnsaved()" type="button">Save</button>
          <button class="notif-button" id="unsaved-discard" onclick="discardUnsaved()" type="button">Discard</button>
          <button class="button-close notif-button align-right" id="unsaved-cancel" type="button">Cancel</button>
        </div>
      </div>
    </div>
  </body>
</html>

<script>
  document.addEventListener("DOMContentLoaded", function (event) {
    element("#editor-form").addEventListener("change", function () {
      if(
        element("#weblink-support").value == "" &&
        element("#weblink-billing").value == "" &&
        element("#weblink-status").value == "" &&
        element("#weblink-social-discord").value == "" &&
        element("#weblink-social-github").value == ""
      ){
        element("#websitelinks-off").checked = true
      } else {
        element("#websitelinks-on").checked = true
      }
    });

    if(
      element("#weblink-support").value == "" &&
      element("#weblink-billing").value == "" &&
      element("#weblink-status").value == "" &&
      element("#weblink-social-discord").value == "" &&
      element("#weblink-social-github").value == ""
    ){
      element("#websitelinks-off").checked = true
    }
  });
  function clearWeblinks() {
    element("#weblink-support").value = ""
    element("#weblink-billing").value = ""
    element("#weblink-status").value = ""
    element("#weblink-social-discord").value = ""
    element("#weblink-social-github").value = ""
  }
</script>

<script>
  document.addEventListener("DOMContentLoaded", function (event) {
    element("#alert-off").addEventListener("click", function () {
      element("#alertcontent").value = null;
      element("#alerticon-disabled").click()
      element("#alertposition-disabled").click()
      element("#alertdismiss-disabled").click()
    });
    element("#alertcontent").addEventListener("change", function () {
      if(element("#alertcontent").value == "") {
        element("#alerticon-disabled").checked = true
        element("#alertposition-disabled").checked = true
        element("#alertdismiss-disabled").checked = true
        element("#alert-off").checked = true
      } else {
        element("#alert-on").click()
        if(element("#alerticon-disabled").checked) {
          element("#alerticon-megaphone").checked = true
        }
        if(element("#alertposition-disabled").checked) {
          element("#alertposition-sticky").checked = true
        }
        if(element("#alertdismiss-disabled").checked) {
          element("#alertdismiss-off").checked = true
        }
      }
    });

    if(element("#alertcontent").value == "") {
      element("#alert-off").checked = true
      element("#alerticon-disabled").checked = true
      element("#alertposition-disabled").checked = true
      element("#alertdismiss-disabled").checked = true
    }
  });
  function alertDepend(event) {
    if(element("#alertcontent").value == '') {
      event.preventDefault()
      element("#alertcontent").focus()
    }
  }
</script>
