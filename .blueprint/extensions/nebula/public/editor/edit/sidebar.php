<?php
  require __DIR__ . '/../../../../../../vendor/autoload.php';
  $app = require_once __DIR__.'/../../../../../../bootstrap/app.php';
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
  <link rel="stylesheet" href="../assets/base.css">
  <script src="../assets/js/navigation.js"></script>
  <script src="../assets/js/editor.js"></script>
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
        <button onclick="navigationAction('general')" to="general" class="navigation-button"><i class="bi bi-sliders2"></i></button>
        <button onclick="navigationAction('palette')" to="palette" class="navigation-button"><i class="bi bi-palette2"></i></button>
        <button onclick="navigationAction('sidebar')" to="sidebar" class="navigation-button active"><i class="bi bi-layout-sidebar-inset"></i></button>
        <button onclick="navigationAction('dashboard')" to="dashboard" class="navigation-button"><i class="bi bi-grid-1x2"></i></button>
        <button onclick="navigationAction('authentication')" to="authentication" class="navigation-button"><i class="bi bi-box-arrow-in-right"></i></button>
        <button onclick="navigationAction('more')" to="more" class="navigation-button"><i class="bi bi-nut"></i></button>
        <div class="save-padding"></div>
        <button onclick="saveAction()" class="save-button"><i class="bi bi-floppy-fill"></i></button>
      </div>
      <div class="editor fade">
        <form action="/admin/extensions/nebula" method="POST" id="editor-form" autocomplete="off">
          <div class="editor-container">
            <h2 class="editor-title">Sidebar</h2>
            <p class="editor-description">Customize how the sidebar behaves and looks.</p>

            <!-- Icon theme -->
            <div class="option">
              <button class="modal-open" onclick="modal('#icon-modal')" type="button" style="float: left"><i class="bi bi-plus-lg"></i></button>
              <p class="option-title with-button">Icon theme</p>
              <!-- Bootstrap -->
              <input type="radio" id="icontheme-bootstrap" name="icon_fallback" value="bootstrap" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "bootstrap") { echo("checked=''"); } ?>>
              <label for="icontheme-bootstrap" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/bootstrap.png" loading="lazy"/>
              </label>
              <!-- Feather -->
              <input type="radio" id="icontheme-feather" name="icon_fallback" value="feather" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "feather") { echo("checked=''"); } ?>>
              <label for="icontheme-feather" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/feather.png" loading="lazy"/>
              </label>
              <!-- Lucide -->
              <input type="radio" id="icontheme-lucide" name="icon_fallback" value="lucide" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "lucide") { echo("checked=''"); } ?>>
              <label for="icontheme-lucide" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/lucide.png" loading="lazy"/>
              </label>
              <!-- Material -->
              <input type="radio" id="icontheme-material" name="icon_fallback" value="material" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "material") { echo("checked=''"); } ?>>
              <label for="icontheme-material" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/material.png" loading="lazy"/>
              </label>
              <!-- Material Light -->
              <input type="radio" id="icontheme-material-light" name="icon_fallback" value="material-light" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "material-light") { echo("checked=''"); } ?>>
              <label for="icontheme-material-light" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/material-light.png" loading="lazy"/>
              </label>
              <!-- Font Awesome -->
              <input type="radio" id="icontheme-fontawesome" name="icon_fallback" value="fontawesome" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "fontawesome") { echo("checked=''"); } ?>>
              <label for="icontheme-fontawesome" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/fontawesome.png" loading="lazy"/>
              </label>
              <!-- Eva Outline -->
              <input type="radio" id="icontheme-eva-outline" name="icon_fallback" value="eva-outline" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "eva-outline") { echo("checked=''"); } ?>>
              <label for="icontheme-eva-outline" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/eva-outline.png" loading="lazy"/>
              </label>
              <!-- Eva Solid -->
              <input type="radio" id="icontheme-eva-solid" name="icon_fallback" value="eva-solid" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "eva-solid") { echo("checked=''"); } ?>>
              <label for="icontheme-eva-solid" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/eva-solid.png" loading="lazy"/>
              </label>
              <!-- Remix Outline -->
              <input type="radio" id="icontheme-remix-outline" name="icon_fallback" value="remix-outline" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "remix-outline") { echo("checked=''"); } ?>>
              <label for="icontheme-remix-outline" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/remix-outline.png" loading="lazy"/>
              </label>
              <!-- Remix Solid -->
              <input type="radio" id="icontheme-remix-solid" name="icon_fallback" value="remix-solid" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "remix-solid") { echo("checked=''"); } ?>>
              <label for="icontheme-remix-solid" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/remix-solid.png" loading="lazy"/>
              </label>
              <!-- Tabler -->
              <input type="radio" id="icontheme-tabler" name="icon_fallback" value="tabler" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "tabler") { echo("checked=''"); } ?>>
              <label for="icontheme-tabler" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/tabler.png" loading="lazy"/>
              </label>
              <!-- Octicons -->
              <input type="radio" id="icontheme-octicons" name="icon_fallback" value="octicons" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "octicons") { echo("checked=''"); } ?>>
              <label for="icontheme-octicons" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/octicons.png" loading="lazy"/>
              </label>
              <!-- Akar Icons -->
              <input type="radio" id="icontheme-akaricons" name="icon_fallback" value="akar-icons" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "akar-icons") { echo("checked=''"); } ?>>
              <label for="icontheme-akaricons" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/akaricons.png" loading="lazy"/>
              </label>
              <!-- Hugeicons Solid -->
              <input type="radio" id="icontheme-hugeicons-solid" name="icon_fallback" value="hugeicons-solid" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "hugeicons-solid") { echo("checked=''"); } ?>>
              <label for="icontheme-hugeicons-solid" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/hugeicons-solid.png" loading="lazy"/>
              </label>
              <!-- Hugeicons Stroke -->
              <input type="radio" id="icontheme-hugeicons-stroke" name="icon_fallback" value="hugeicons-stroke" class="hidden" <?php if($blueprint->dbGet("nebula", "icon_fallback") == "hugeicons-stroke") { echo("checked=''"); } ?>>
              <label for="icontheme-hugeicons-stroke" class="option-radio sm aspect-square">
                <img src="../assets/images/sidebar/icontheme/hugeicons-stroke.png" loading="lazy"/>
              </label>
              <p class="option-footer">Pick the icon theme that best suites your panel.</p>
            </div>

            <!-- Type -->
            <div class="option">
              <button class="modal-open" onclick="modal('#sidebar-modal')" type="button" style="float: left"><i class="bi bi-plus-lg"></i></button>
              <p class="option-title with-button">Type</p>
              <!-- Icons -->
              <input type="radio" id="type-icons" name="sidebar_full" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_full") == "0") { echo("checked=''"); } ?>>
              <label for="type-icons" class="option-radio">
                <img src="../assets/images/sidebar/type/icons.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Icon + text -->
              <input type="radio" id="type-wide" name="sidebar_full" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_full") == "1") { echo("checked=''"); } ?>>
              <label for="type-wide" class="option-radio">
                <img src="../assets/images/sidebar/type/wide.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Choose between an icon-only sidebar or one with text lables. Sidebar with text-lables has limited customization support.</p>
            </div>

            <!-- Hover animation -->
            <div class="option">
              <p class="option-title">Hover animation</p>
              <!-- Popout -->
              <input type="radio" id="hover-popout" name="sidebar_hover" value="popout" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_hover") == "popout") { echo("checked=''"); } ?>>
              <label for="hover-popout" class="option-radio">
                <img src="../assets/images/sidebar/hover/popout.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Expand -->
              <input type="radio" id="hover-expand" name="sidebar_hover" value="expand" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_hover") == "expand") { echo("checked=''"); } ?>>
              <label for="hover-expand" class="option-radio">
                <img src="../assets/images/sidebar/hover/expand.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="hover-disabled" name="sidebar_hover" value="disabled" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_hover") == "disabled") { echo("checked=''"); } ?>>
              <label for="hover-disabled" class="option-radio">
                <img src="../assets/images/sidebar/hover/disabled.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Optionally add a hover animation to the sidebar buttons.</p>
            </div>

            <!-- Appearance -->
            <div class="option">
              <p class="option-title">Appearance</p>
              <!-- Solid -->
              <input type="radio" id="appearance-solid" name="sidebar_background" value="default" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_background") == "default") { echo("checked=''"); } ?>>
              <label for="appearance-solid" class="option-radio">
                <img src="../assets/images/sidebar/appearance/solid.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Glass -->
              <input type="radio" id="appearance-glass" name="sidebar_background" value="blurred" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_background") == "blurred") { echo("checked=''"); } ?>>
              <label for="appearance-glass" class="option-radio">
                <img src="../assets/images/sidebar/appearance/glass.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Customize the appearance of the sidebar.</p>
            </div>

            <!-- Global navigation -->
            <div class="option">
              <p class="option-title">Global navigation</p>
              <!-- Show -->
              <input type="radio" id="globalnavigation-show" name="sidebar_always_visible_buttons" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_always_visible_buttons") == "1") { echo("checked=''"); } ?>>
              <label for="globalnavigation-show" class="option-radio">
                <img src="../assets/images/sidebar/globalnavigation/show.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Hide -->
              <input type="radio" id="globalnavigation-hide" name="sidebar_always_visible_buttons" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_always_visible_buttons") == "0") { echo("checked=''"); } ?>>
              <label for="globalnavigation-hide" class="option-radio">
                <img src="../assets/images/sidebar/globalnavigation/hide.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Hide global navigation on account and server pages.</p>
            </div>

            <!-- Separators -->
            <div class="option">
              <p class="option-title">Separators</p>
              <!-- Enabled -->
              <input type="radio" id="separators-enabled" name="sidebar_separators" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_separators") == "1") { echo("checked=''"); } ?>>
              <label for="separators-enabled" class="option-radio">
                <img src="../assets/images/sidebar/separators/on.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="separators-disabled" name="sidebar_separators" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_separators") == "0") { echo("checked=''"); } ?>>
              <label for="separators-disabled" class="option-radio">
                <img src="../assets/images/sidebar/separators/off.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Allows hiding of sidebar category separators.</p>
            </div>

            <!-- Border radius -->
            <div class="option">
              <p class="option-title">Border radius</p>
              <div class="option-container">
                <input type="range" id="border-radius" min="0" max="20" name="sidebar_border_radius" value="<?php echo $blueprint->dbGet("nebula", "sidebar_border_radius"); ?>" step="1" class="option-slider">
                <script>
                  document.addEventListener('DOMContentLoaded', () => {
                    const slider = document.querySelector('.option-container #border-radius');
                    const tip = tippy(slider, {
                      content: Math.floor((slider.value)) + 'px',
                      trigger: 'manual',
                      arrow: false,
                      animation: 'shift-away'
                    });

                    slider.addEventListener('input', () => {
                      tip.setContent(`${Math.floor((slider.value)) + 'px'}`);
                      tip.show();
                    });

                    slider.addEventListener('blur', () => {
                      tip.hide();
                    });
                  });
                </script>
              </div>
              <p class="option-footer">Adjust the border radius of certain sidebar elements.</p>
            </div>
          </div>

          <!-- Icon modal -->
          <div class="editor-modal" id="icon-modal">
            <button class="modal-close" onclick="closeModal('#icon-modal')" type="button" style="float: left"><i class="bi bi-chevron-left"></i></button>
            <h2 class="editor-title with-button">Icon theme</h2>
            <p class="editor-description">Create icon overrides and change the icon scale to make the sidebar truely yours.</p>

            <div class="editor-alert" id="type-wide-alert">
              <p class="alert-text">Options listed here only work on <u>icon-only</u> sidebar. Editing options on this page will automatically <u>switch your sidebar setting</u> back to icon-only.</p>
            </div>

            <div class="opacity-container" id="icon-modal-opacity">
              <!-- Icon scale -->
              <div class="option">
                <p class="option-title">Icon scale</p>
                <div class="option-container">
                  <input type="range" id="icon-scale" min="0.1" max="1" name="icon_scale" value="<?php echo $blueprint->dbGet("nebula", "icon_scale"); ?>" step="0.05" class="option-slider">
                  <script>
                    document.addEventListener('DOMContentLoaded', () => {
                      const slider = document.querySelector('.option-container #icon-scale');
                      const tip = tippy(slider, {
                        content: Math.floor((slider.value * 100)) + '%',
                        trigger: 'manual',
                        arrow: false,
                        animation: 'shift-away'
                      });

                      slider.addEventListener('input', () => {
                        tip.setContent(`${Math.floor((slider.value * 100)) + '%'}`);
                        tip.show();
                        element("#type-icons").click()
                      });

                      slider.addEventListener('blur', () => {
                        tip.hide();
                      });
                    });
                  </script>
                </div>
                <p class="option-footer">Customize the size sidebar icons display at.</p>
              </div>

              <!-- Global -->
              <div class="option">
                <p class="option-title">Global</p>
                <!-- Home -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-house-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-home" name="sidebar_home" class="option-input with-icon" placeholder="https://nebula.style/home.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_home"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-home)', { content: "Home", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Admin -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-gear-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-admin" name="sidebar_admin" class="option-input with-icon" placeholder="https://nebula.style/admin.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_admin"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-admin)', { content: "Admin", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Account -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-person-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-account" name="sidebar_account" class="option-input with-icon" placeholder="https://nebula.style/account.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_account"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-account)', { content: "Account", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Logout -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-door-closed-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-logout" name="sidebar_logout" class="option-input with-icon" placeholder="https://nebula.style/logout.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_logout"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-logout)', { content: "Logout", arrow: false, animation: 'shift-away' }); </script>
                </div>
              </div>

              <!-- Account -->
              <div class="option">
                <p class="option-title">Account</p>
                <!-- Account -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-emoji-smile-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-account-account" name="sidebar_account_account" class="option-input with-icon" placeholder="https://nebula.style/account.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_account_account"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-account-account)', { content: "Account", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- API -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-globe"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-account-api" name="sidebar_account_api" class="option-input with-icon" placeholder="https://nebula.style/api.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_account_api"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-account-api)', { content: "API Tokens", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- SSH -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-key-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-account-ssh" name="sidebar_account_ssh" class="option-input with-icon" placeholder="https://nebula.style/ssh.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_account_ssh"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-account-ssh)', { content: "SSH Keys", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Activity -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-clipboard-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-account-activity" name="sidebar_account_activity" class="option-input with-icon" placeholder="https://nebula.style/activity.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_account_activity"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-account-activity)', { content: "Activity", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- More -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-three-dots"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-account-more" name="sidebar_account_more" class="option-input with-icon" placeholder="https://nebula.style/more.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_account_more"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-account-more)', { content: "More", arrow: false, animation: 'shift-away' }); </script>
                </div>
              </div>

              <!-- Server -->
              <div class="option">
                <p class="option-title">Server</p>
                <!-- Terminal -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-terminal-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-terminal" name="sidebar_server_terminal" class="option-input with-icon" placeholder="https://nebula.style/terminal.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_terminal"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-terminal)', { content: "Terminal", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Files -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-folder-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-files" name="sidebar_server_files" class="option-input with-icon" placeholder="https://nebula.style/files.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_files"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-files)', { content: "Files", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Databases -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-database-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-databases" name="sidebar_server_databases" class="option-input with-icon" placeholder="https://nebula.style/databases.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_databases"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-databases)', { content: "Databases", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Schedules -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-calendar-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-schedules" name="sidebar_server_schedules" class="option-input with-icon" placeholder="https://nebula.style/schedules.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_schedules"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-schedules)', { content: "Schedules", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Users -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-people-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-users" name="sidebar_server_users" class="option-input with-icon" placeholder="https://nebula.style/users.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_users"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-users)', { content: "Subusers", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Backups -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-archive-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-backups" name="sidebar_server_backups" class="option-input with-icon" placeholder="https://nebula.style/backups.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_backups"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-backups)', { content: "Backups", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Network -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-reception-4"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-network" name="sidebar_server_network" class="option-input with-icon" placeholder="https://nebula.style/network.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_network"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-network)', { content: "Network", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Startup -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-plug-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-startup" name="sidebar_server_startup" class="option-input with-icon" placeholder="https://nebula.style/startup.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_startup"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-startup)', { content: "Startup", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Settings -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-gear-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-settings" name="sidebar_server_settings" class="option-input with-icon" placeholder="https://nebula.style/settings.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_settings"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-settings)', { content: "Settings", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- Activity -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-clipboard-fill"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-activity" name="sidebar_server_activity" class="option-input with-icon" placeholder="https://nebula.style/activity.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_activity"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-activity)', { content: "Activity", arrow: false, animation: 'shift-away' }); </script>
                </div>
                <!-- More -->
                <div class="option-container with-margin">
                  <span class="option-icon"><i class="bi bi-three-dots"></i></span>
                  <input onclick="element('#type-icons').click()" type="text" id="icon-server-more" name="sidebar_server_more" class="option-input with-icon" placeholder="https://nebula.style/more.png" value="<?php echo $blueprint->dbGet("nebula", "sidebar_server_more"); ?>">
                  <script> tippy('.option-container:has(.option-icon + #icon-server-more)', { content: "More", arrow: false, animation: 'shift-away' }); </script>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar modal -->
          <div class="editor-modal" id="sidebar-modal">
            <button class="modal-close" onclick="closeModal('#sidebar-modal')" type="button" style="float: left"><i class="bi bi-chevron-left"></i></button>
            <h2 class="editor-title with-button">Sidebar type</h2>
            <p class="editor-description">Additional options related to the sidebar type setting.</p>

            <div class="editor-alert">
              <p class="alert-text">Options listed here are specific to your <u>selected sidebar type</u>. Options that do not work with your selected sidebar type have been hidden.</p>
            </div>

            <!-- Tooltip -->
            <div class="option" id="option-tooltip">
              <p class="option-title">Tooltip</p>
              <!-- Aligned -->
              <input type="radio" id="tooltip-aligned" name="sidebar_hover_tooltip" value="2" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_hover_tooltip") == "2") { echo("checked=''"); } ?>>
              <label onclick="element('#type-icons').click()" for="tooltip-aligned" class="option-radio">
                <img src="../assets/images/sidebar/tooltip/aligned.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Default -->
              <input type="radio" id="tooltip-default" name="sidebar_hover_tooltip" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_hover_tooltip") == "1") { echo("checked=''"); } ?>>
              <label onclick="element('#type-icons').click()" for="tooltip-default" class="option-radio">
                <img src="../assets/images/sidebar/tooltip/default.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="tooltip-off" name="sidebar_hover_tooltip" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_hover_tooltip") == "0") { echo("checked=''"); } ?>>
              <label onclick="element('#type-icons').click()" for="tooltip-off" class="option-radio">
                <img src="../assets/images/sidebar/tooltip/off.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Choose how the sidebar hover tooltip should be displayed.</p>
            </div>

            <!-- Button style -->
            <div class="option" id="option-buttonstyle">
              <p class="option-title">Button style</p>
              <!-- Default -->
              <input type="radio" id="buttonstyle-default" name="sidebar_buttonstyle" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_buttonstyle") == "0") { echo("checked=''"); } ?>>
              <label onclick="element('#type-wide').click()" for="buttonstyle-default" class="option-radio">
                <img src="../assets/images/sidebar/buttonstyle/default.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Line -->
              <input type="radio" id="buttonstyle-line" name="sidebar_buttonstyle" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_buttonstyle") == "1") { echo("checked=''"); } ?>>
              <label onclick="element('#type-wide').click()" for="buttonstyle-line" class="option-radio">
                <img src="../assets/images/sidebar/buttonstyle/line.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Outline -->
              <input type="radio" id="buttonstyle-outline" name="sidebar_buttonstyle" value="2" class="hidden" <?php if($blueprint->dbGet("nebula", "sidebar_buttonstyle") == "2") { echo("checked=''"); } ?>>
              <label onclick="element('#type-wide').click()" for="buttonstyle-outline" class="option-radio">
                <img src="../assets/images/sidebar/buttonstyle/outline.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Customize how buttons display at in the full-size sidebar.</p>
            </div>

            <!-- Custom logo -->
            <div class="option" id="option-customlogo">
              <p class="option-title">Custom logo</p>
              <div class="option-container with-margin" onclick="element('#type-wide').click()">
                <span class="option-icon"><i class="bi bi-image"></i></span>
                <input type="text" id="custom-logo-field" name="sidebar_customlogo" class="option-input with-icon" placeholder="https://nebula.style/logo.jpg" value="<?php echo $blueprint->dbGet("nebula", "sidebar_customlogo"); ?>">
              </div>
              <p class="option-footer">Add your own logo to the full-width sidebar.</p>
            </div>
          </div>

          <div id="editor-submit">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="_endpoint" value="/extensions/nebula/editor/edit/sidebar.php">
            <input type="hidden" name="_method" value="PATCH">
            <button type="submit" class="hidden" id="submit"></button>
          </div>
        </form>
      </div>
      <div class="preview fade">
        <iframe
          title="Preview"
          class="preview-frame match-dashboard-bg"
          src="/"
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
    element("#type-wide-alert").style.display = "none";
    element("#option-tooltip").style.display = "none";
    element("#option-buttonstyle").style.display = "none";
    element("#option-customlogo").style.display = "none";

    element("#type-wide").addEventListener("click", function () {
      element("#type-wide-alert").style.display = "block";
      element("#option-tooltip").style.display = "none";
      element("#option-buttonstyle").style.display = "block";
      element("#option-customlogo").style.display = "block";
      element("#icon-modal-opacity").style.opacity = 0.4;
      document.getElementById("tooltip-off").click()
    });
    element("#type-icons").addEventListener("click", function () {
      element("#type-wide-alert").style.display = "none";
      element("#option-tooltip").style.display = "block";
      element("#option-buttonstyle").style.display = "none";
      element("#option-customlogo").style.display = "none";
      element("#icon-modal-opacity").style.opacity = 1;
    });

    if(element("#type-wide").checked) {
      element("#type-wide-alert").style.display = "block";
      element("#option-tooltip").style.display = "none";
      element("#option-buttonstyle").style.display = "block";
      element("#option-customlogo").style.display = "block";
      element("#icon-modal-opacity").style.opacity = 0.4;
      document.getElementById("tooltip-off").click()
    } else {
      element("#option-tooltip").style.display = "block";
      element("#option-buttonstyle").style.display = "none";
      element("#option-customlogo").style.display = "none";
      element("#icon-modal-opacity").style.opacity = 1;
    }
  });
</script>
