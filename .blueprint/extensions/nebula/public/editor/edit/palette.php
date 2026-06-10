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
  <!-- coloris.js --> <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@latest/dist/coloris.min.css"/>
  <!-- coloris.js --> <script src="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@latest/dist/coloris.min.js"></script>
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
        <button onclick="navigationAction('palette')" to="palette" class="navigation-button active"><i class="bi bi-palette2"></i></button>
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
            <h2 class="editor-title">Palette</h2>
            <p class="editor-description">Modify the color scheme of Nebula.</p>

            <!-- Presets -->
            <div class="option">
              <p class="option-title">Presets</p>
              <!-- Default -->
              <input type="radio" id="preset-default" name="palette_preset" value="" class="hidden">
              <label for="preset-default" class="option-radio sm no-bg" onclick="applyPalette(event, 'default')">
                <img src="../assets/images/palette/presets/default.png" class="aspect-1:0.55"/>
              </label>
              <!-- Slate -->
              <input type="radio" id="preset-slate" name="palette_preset" value="" class="hidden">
              <label for="preset-slate" class="option-radio sm no-bg" onclick="applyPalette(event, 'slate')">
                <img src="../assets/images/palette/presets/slate.png" class="aspect-1:0.55"/>
              </label>
              <!-- Pyro -->
              <input type="radio" id="preset-pyro" name="palette_preset" value="" class="hidden">
              <label for="preset-pyro" class="option-radio sm no-bg" onclick="applyPalette(event, 'pyro')">
                <img src="../assets/images/palette/presets/pyro.png" class="aspect-1:0.55"/>
              </label>
              <!-- Leaf -->
              <input type="radio" id="preset-leaf" name="palette_preset" value="" class="hidden">
              <label for="preset-leaf" class="option-radio sm no-bg" onclick="applyPalette(event, 'leaf')">
                <img src="../assets/images/palette/presets/leaf.png" class="aspect-1:0.55"/>
              </label>
              <!-- Catppuccin -->
              <input type="radio" id="preset-catppuccin" name="palette_preset" value="" class="hidden">
              <label for="preset-catppuccin" class="option-radio sm no-bg" onclick="applyPalette(event, 'catppuccin')">
                <img src="../assets/images/palette/presets/catppuccin.png" class="aspect-1:0.55"/>
              </label>
              <p class="option-footer">Start from a pre-made color palette.</p>
            </div>

            <!-- Dashboard palette -->
            <div class="option">
              <p class="option-title">Dashboard palette</p>
              <div>
                <input type="text" class="option-color" placeholder="#000000" id="palette_dashboard_1" name="palette_dashboard_1" value="<?php echo $blueprint->dbGet("nebula", "palette_dashboard_1"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_dashboard_2" name="palette_dashboard_2" value="<?php echo $blueprint->dbGet("nebula", "palette_dashboard_2"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_dashboard_3" name="palette_dashboard_3" value="<?php echo $blueprint->dbGet("nebula", "palette_dashboard_3"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_dashboard_4" name="palette_dashboard_4" value="<?php echo $blueprint->dbGet("nebula", "palette_dashboard_4"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_dashboard_5" name="palette_dashboard_5" value="<?php echo $blueprint->dbGet("nebula", "palette_dashboard_5"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_dashboard_6" name="palette_dashboard_6" value="<?php echo $blueprint->dbGet("nebula", "palette_dashboard_6"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_dashboard_7" name="palette_dashboard_7" value="<?php echo $blueprint->dbGet("nebula", "palette_dashboard_7"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_dashboard_8" name="palette_dashboard_8" value="<?php echo $blueprint->dbGet("nebula", "palette_dashboard_8"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_dashboard_9" name="palette_dashboard_9" value="<?php echo $blueprint->dbGet("nebula", "palette_dashboard_9"); ?>" data-coloris onclick="focusDash()">
              </div>
            </div>

            <!-- Utility colors -->
            <div class="option">
              <p class="option-title">Utility colors</p>
              <div>
                <input type="text" class="option-color" placeholder="#000000" id="palette_status_online" name="palette_status_online" value="<?php echo $blueprint->dbGet("nebula", "palette_status_online"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_status_starting" name="palette_status_starting" value="<?php echo $blueprint->dbGet("nebula", "palette_status_starting"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_status_error" name="palette_status_error" value="<?php echo $blueprint->dbGet("nebula", "palette_status_error"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_status_offline" name="palette_status_offline" value="<?php echo $blueprint->dbGet("nebula", "palette_status_offline"); ?>" data-coloris onclick="focusDash()">
              </div>
            </div>

            <!-- Sidebar palette -->
            <div class="option">
              <p class="option-title">Sidebar palette</p>
              <div>
                <input type="text" class="option-color" placeholder="#000000" id="palette_sidebar_1" name="palette_sidebar_1" value="<?php echo $blueprint->dbGet("nebula", "palette_sidebar_1"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_sidebar_2" name="palette_sidebar_2" value="<?php echo $blueprint->dbGet("nebula", "palette_sidebar_2"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_sidebar_3" name="palette_sidebar_3" value="<?php echo $blueprint->dbGet("nebula", "palette_sidebar_3"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_sidebar_4" name="palette_sidebar_4" value="<?php echo $blueprint->dbGet("nebula", "palette_sidebar_4"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_sidebar_5" name="palette_sidebar_5" value="<?php echo $blueprint->dbGet("nebula", "palette_sidebar_5"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_sidebar_6" name="palette_sidebar_6" value="<?php echo $blueprint->dbGet("nebula", "palette_sidebar_6"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_sidebar_7" name="palette_sidebar_7" value="<?php echo $blueprint->dbGet("nebula", "palette_sidebar_7"); ?>" data-coloris onclick="focusDash()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_sidebar_8" name="palette_sidebar_8" value="<?php echo $blueprint->dbGet("nebula", "palette_sidebar_8"); ?>" data-coloris onclick="focusDash()">
              </div>
            </div>

            <!-- Authentication palette -->
            <div class="option">
              <p class="option-title">Authentication palette</p>
              <div>
                <input type="text" class="option-color" placeholder="#000000" id="palette_auth_1" name="palette_auth_1" value="<?php echo $blueprint->dbGet("nebula", "palette_auth_1"); ?>" data-coloris onclick="focusAuth()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_auth_2" name="palette_auth_2" value="<?php echo $blueprint->dbGet("nebula", "palette_auth_2"); ?>" data-coloris onclick="focusAuth()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_auth_3" name="palette_auth_3" value="<?php echo $blueprint->dbGet("nebula", "palette_auth_3"); ?>" data-coloris onclick="focusAuth()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_auth_4" name="palette_auth_4" value="<?php echo $blueprint->dbGet("nebula", "palette_auth_4"); ?>" data-coloris onclick="focusAuth()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_auth_5" name="palette_auth_5" value="<?php echo $blueprint->dbGet("nebula", "palette_auth_5"); ?>" data-coloris onclick="focusAuth()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_auth_6" name="palette_auth_6" value="<?php echo $blueprint->dbGet("nebula", "palette_auth_6"); ?>" data-coloris onclick="focusAuth()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_auth_7" name="palette_auth_7" value="<?php echo $blueprint->dbGet("nebula", "palette_auth_7"); ?>" data-coloris onclick="focusAuth()">
                <input type="text" class="option-color" placeholder="#000000" id="palette_auth_8" name="palette_auth_8" value="<?php echo $blueprint->dbGet("nebula", "palette_auth_8"); ?>" data-coloris onclick="focusAuth()">
              </div>
            </div>

          </div>

          <div id="editor-picker"></div>
          <div id="editor-submit">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="_endpoint" value="/extensions/nebula/editor/edit/palette.php">
            <input type="hidden" name="_method" value="PATCH">
            <button type="submit" class="hidden" id="submit"></button>
          </div>
        </form>
      </div>
      <div class="preview fade">
        <iframe
          title="Preview"
          class="preview-frame sm"
          src="/extensions/nebula/preview/multipreview.php"
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
  Coloris({
    themeMode: 'dark',
    alpha: false,
    format: 'hex',
    theme: 'pill',
    swatches: [
      '#ff6666',
      '#ffc466',
      '#ccff66',
      '#69ff66',
      '#66ffa1',
      '#66ffd6',
      '#66e6ff',
      '#66b0ff',
      '#6678ff',
      '#9466ff',
      '#cc66ff',
      '#ff66d9'
    ],
  });

  // Define presets
  const preset_default = [
    { item: element('#palette_sidebar_1'), value: "#c8d2dd" },
    { item: element('#palette_sidebar_2'), value: "#dbe4ec" },
    { item: element('#palette_sidebar_3'), value: "#211c1e" },
    { item: element('#palette_sidebar_4'), value: "#262626" },
    { item: element('#palette_sidebar_5'), value: "#3a3a3a" },
    { item: element('#palette_sidebar_6'), value: "#383535" },
    { item: element('#palette_sidebar_7'), value: "#0C090A" },
    { item: element('#palette_sidebar_8'), value: "#343538" },
    { item: element('#palette_dashboard_1'), value: "#c8d2dd" },
    { item: element('#palette_dashboard_2'), value: "#788189" },
    { item: element('#palette_dashboard_3'), value: "#1b1b1b" },
    { item: element('#palette_dashboard_4'), value: "#262626" },
    { item: element('#palette_dashboard_5'), value: "#3a3a3a" },
    { item: element('#palette_dashboard_6'), value: "#474747" },
    { item: element('#palette_dashboard_7'), value: "#0c090a" },
    { item: element('#palette_dashboard_8'), value: "#474849" },
    { item: element('#palette_dashboard_9'), value: "#525354" },
    { item: element('#palette_status_online'), value: "#00dd0f" },
    { item: element('#palette_status_starting'), value: "#e5a309" },
    { item: element('#palette_status_error'), value: "#ca231d" },
    { item: element('#palette_status_offline'), value: "#525354" },
    { item: element('#palette_auth_1'), value: "#080808" },
    { item: element('#palette_auth_2'), value: "#0f0f0f" },
    { item: element('#palette_auth_3'), value: "#1f1f1f" },
    { item: element('#palette_auth_4'), value: "#2c2c2c" },
    { item: element('#palette_auth_5'), value: "#ff5151" },
    { item: element('#palette_auth_6'), value: "#3b82f6" },
    { item: element('#palette_auth_7'), value: "#505050" },
    { item: element('#palette_auth_8'), value: "#e4e8ec" }
  ];
  const preset_slate = [
    { item: element('#palette_sidebar_1'), value: "#ffffff" },
    { item: element('#palette_sidebar_2'), value: "#ffffff" },
    { item: element('#palette_sidebar_3'), value: "#251f30" },
    { item: element('#palette_sidebar_4'), value: "#23293e" },
    { item: element('#palette_sidebar_5'), value: "#23293e" },
    { item: element('#palette_sidebar_6'), value: "#7a98ff" },
    { item: element('#palette_sidebar_7'), value: "#040814" },
    { item: element('#palette_sidebar_8'), value: "#7a98ff" },
    { item: element('#palette_dashboard_1'), value: "#e9eaee" },
    { item: element('#palette_dashboard_2'), value: "#7a98ff" },
    { item: element('#palette_dashboard_3'), value: "#251f30" },
    { item: element('#palette_dashboard_4'), value: "#2b2f3e" },
    { item: element('#palette_dashboard_5'), value: "#303443" },
    { item: element('#palette_dashboard_6'), value: "#363e57" },
    { item: element('#palette_dashboard_7'), value: "#040814" },
    { item: element('#palette_dashboard_8'), value: "#3a435c" },
    { item: element('#palette_dashboard_9'), value: "#4f6295" },
    { item: element('#palette_status_online'), value: "#2cdd2f" },
    { item: element('#palette_status_starting'), value: "#dbc025" },
    { item: element('#palette_status_error'), value: "#bc362f" },
    { item: element('#palette_status_offline'), value: "#787474" },
    { item: element('#palette_auth_1'), value: "#040814" },
    { item: element('#palette_auth_2'), value: "#251f30" },
    { item: element('#palette_auth_3'), value: "#2b2f3e" },
    { item: element('#palette_auth_4'), value: "#3a435c" },
    { item: element('#palette_auth_5'), value: "#e18989" },
    { item: element('#palette_auth_6'), value: "#4f6295" },
    { item: element('#palette_auth_7'), value: "#47526a" },
    { item: element('#palette_auth_8'), value: "#e9eaee" }
  ];
  const preset_pyro = [
    { item: element('#palette_sidebar_1'), value: "#ffffff" },
    { item: element('#palette_sidebar_2'), value: "#ffffff" },
    { item: element('#palette_sidebar_3'), value: "#131010" },
    { item: element('#palette_sidebar_4'), value: "#1c1818" },
    { item: element('#palette_sidebar_5'), value: "#252020" },
    { item: element('#palette_sidebar_6'), value: "#fb4242" },
    { item: element('#palette_sidebar_7'), value: "#010101" },
    { item: element('#palette_sidebar_8'), value: "#fb4242" },
    { item: element('#palette_dashboard_1'), value: "#e9eaee" },
    { item: element('#palette_dashboard_2'), value: "#fb4242" },
    { item: element('#palette_dashboard_3'), value: "#131010" },
    { item: element('#palette_dashboard_4'), value: "#1c1818" },
    { item: element('#palette_dashboard_5'), value: "#252020" },
    { item: element('#palette_dashboard_6'), value: "#2f2828" },
    { item: element('#palette_dashboard_7'), value: "#010101" },
    { item: element('#palette_dashboard_8'), value: "#fb4242" },
    { item: element('#palette_dashboard_9'), value: "#3a3232" },
    { item: element('#palette_status_online'), value: "#42e33d" },
    { item: element('#palette_status_starting'), value: "#e8bd15" },
    { item: element('#palette_status_error'), value: "#fb4242" },
    { item: element('#palette_status_offline'), value: "#524b4b" },
    { item: element('#palette_auth_1'), value: "#010101" },
    { item: element('#palette_auth_2'), value: "#010101" },
    { item: element('#palette_auth_3'), value: "#131010" },
    { item: element('#palette_auth_4'), value: "#fb4242" },
    { item: element('#palette_auth_5'), value: "#fb4242" },
    { item: element('#palette_auth_6'), value: "#fb4242" },
    { item: element('#palette_auth_7'), value: "#505050" },
    { item: element('#palette_auth_8'), value: "#ffffff" }
  ];
  const preset_leaf = [
    { item: element('#palette_sidebar_1'), value: "#e9eaee" },
    { item: element('#palette_sidebar_2'), value: "#e9eaee" },
    { item: element('#palette_sidebar_3'), value: "#161519" },
    { item: element('#palette_sidebar_4'), value: "#121114" },
    { item: element('#palette_sidebar_5'), value: "#0c0c0e" },
    { item: element('#palette_sidebar_6'), value: "#09a05c" },
    { item: element('#palette_sidebar_7'), value: "#1f1e24" },
    { item: element('#palette_sidebar_8'), value: "#09a05c" },
    { item: element('#palette_dashboard_1'), value: "#e9eaee" },
    { item: element('#palette_dashboard_2'), value: "#09a05c" },
    { item: element('#palette_dashboard_3'), value: "#161519" },
    { item: element('#palette_dashboard_4'), value: "#1b1a1f" },
    { item: element('#palette_dashboard_5'), value: "#272530" },
    { item: element('#palette_dashboard_6'), value: "#2f2d3d" },
    { item: element('#palette_dashboard_7'), value: "#1f1e24" },
    { item: element('#palette_dashboard_8'), value: "#09a05c" },
    { item: element('#palette_dashboard_9'), value: "#2bc27c" },
    { item: element('#palette_status_online'), value: "#09a05c" },
    { item: element('#palette_status_starting'), value: "#b98538" },
    { item: element('#palette_status_error'), value: "#9b3d3c" },
    { item: element('#palette_status_offline'), value: "#3e3a3a" },
    { item: element('#palette_auth_1'), value: "#1f1e24" },
    { item: element('#palette_auth_2'), value: "#101013" },
    { item: element('#palette_auth_3'), value: "#19191e" },
    { item: element('#palette_auth_4'), value: "#19191e" },
    { item: element('#palette_auth_5'), value: "#f77272" },
    { item: element('#palette_auth_6'), value: "#2bc27c" },
    { item: element('#palette_auth_7'), value: "#494957" },
    { item: element('#palette_auth_8'), value: "#101013" }
  ];
  const preset_catppuccin = [
    { item: element('#palette_sidebar_1'), value: "#cdd6f4" },
    { item: element('#palette_sidebar_2'), value: "#cdd6f4" },
    { item: element('#palette_sidebar_3'), value: "#181825" },
    { item: element('#palette_sidebar_4'), value: "#11111b" },
    { item: element('#palette_sidebar_5'), value: "#11111b" },
    { item: element('#palette_sidebar_6'), value: "#cba6f7" },
    { item: element('#palette_sidebar_7'), value: "#1e1e2e" },
    { item: element('#palette_sidebar_8'), value: "#cba6f7" },
    { item: element('#palette_dashboard_1'), value: "#cdd6f4" },
    { item: element('#palette_dashboard_2'), value: "#cba6f7" },
    { item: element('#palette_dashboard_3'), value: "#181825" },
    { item: element('#palette_dashboard_4'), value: "#242635" },
    { item: element('#palette_dashboard_5'), value: "#313244" },
    { item: element('#palette_dashboard_6'), value: "#45475a" },
    { item: element('#palette_dashboard_7'), value: "#1e1e2e" },
    { item: element('#palette_dashboard_8'), value: "#cba6f7" },
    { item: element('#palette_dashboard_9'), value: "#cba6f7" },
    { item: element('#palette_status_online'), value: "#cba6f7" },
    { item: element('#palette_status_starting'), value: "#e39e79" },
    { item: element('#palette_status_error'), value: "#f38ba8" },
    { item: element('#palette_status_offline'), value: "#45475a" },
    { item: element('#palette_auth_1'), value: "#1e1e2e" },
    { item: element('#palette_auth_2'), value: "#11111b" },
    { item: element('#palette_auth_3'), value: "#181825" },
    { item: element('#palette_auth_4'), value: "#181825" },
    { item: element('#palette_auth_5'), value: "#f77272" },
    { item: element('#palette_auth_6'), value: "#cba6f7" },
    { item: element('#palette_auth_7'), value: "#a6adc8" },
    { item: element('#palette_auth_8'), value: "#11111b" }
  ];

  function applyPalette(event, palette) {
    event.preventDefault()
    if(palette == "default") {
      for (let i = preset_default.length - 1; i >= 0; i--) {
        const colors = preset_default[i];
        colors.item.value = colors.value;
        colors.item.dispatchEvent(new Event('input', { bubbles: true }));
      }
    } else if(palette == "slate") {
      for (let i = preset_slate.length - 1; i >= 0; i--) {
        const colors = preset_slate[i];
        colors.item.value = colors.value;
        colors.item.dispatchEvent(new Event('input', { bubbles: true }));
      }
    } else if(palette == "pyro") {
      for (let i = preset_pyro.length - 1; i >= 0; i--) {
        const colors = preset_pyro[i];
        colors.item.value = colors.value;
        colors.item.dispatchEvent(new Event('input', { bubbles: true }));
      }
    } else if(palette == "leaf") {
      for (let i = preset_leaf.length - 1; i >= 0; i--) {
        const colors = preset_leaf[i];
        colors.item.value = colors.value;
        colors.item.dispatchEvent(new Event('input', { bubbles: true }));
      }
    } else if(palette == "catppuccin") {
      for (let i = preset_catppuccin.length - 1; i >= 0; i--) {
        const colors = preset_catppuccin[i];
        colors.item.value = colors.value;
        colors.item.dispatchEvent(new Event('input', { bubbles: true }));
      }
    }
    refreshPreview()
    showSaveButton()
  }

  function refreshPreview() {
    var Transparency = "<?php
      $transparency = $blueprint->dbGet("nebula", "dashboard_transparency");
      if($transparency == "1") { echo "BB"; }
      elseif($transparency == "2") { echo "99"; }
      elseif($transparency == "3") { echo "60"; }
    ?>"

    var frameDocument = element(".preview-frame").contentDocument;
    var dashPreview = frameDocument.querySelector("#dashPreview").contentDocument;
    var authPreview = frameDocument.querySelector("#authPreview").contentDocument;
    let dashRoot = dashPreview.querySelector(':root');
    let authRoot = authPreview.querySelector(':root');

    // sidebar
    dashRoot.style.setProperty('--sidebarPrimary', element('#palette_sidebar_1').value);
    dashRoot.style.setProperty('--sidebarPrimaryHover', element('#palette_sidebar_2').value);
    dashRoot.style.setProperty('--sidebarSecondary', element('#palette_sidebar_3').value);
    dashRoot.style.setProperty('--sidebarSecondaryHover', element('#palette_sidebar_4').value);
    dashRoot.style.setProperty('--sidebarSecondaryActive', element('#palette_sidebar_5').value);
    dashRoot.style.setProperty('--sidebarSecondarySelected', element('#palette_sidebar_6').value);
    dashRoot.style.setProperty('--sidebarBackground', element('#palette_sidebar_7').value);
    dashRoot.style.setProperty('--sidebarButtonActive', element('#palette_sidebar_8').value);

    // page
    dashRoot.style.setProperty('--pagePrimary', element('#palette_dashboard_1').value);
    dashRoot.style.setProperty('--pagePrimaryHover', element('#palette_dashboard_2').value);
    dashRoot.style.setProperty('--pageSecondary', element('#palette_dashboard_3').value+Transparency);
    dashRoot.style.setProperty('--pageSecondaryHover', element('#palette_dashboard_4').value+Transparency);
    dashRoot.style.setProperty('--pageSecondaryActive', element('#palette_dashboard_5').value+Transparency);
    dashRoot.style.setProperty('--pageSecondarySelected', element('#palette_dashboard_6').value+Transparency);
    dashRoot.style.setProperty('--pageBackground', element('#palette_dashboard_7').value);
    dashRoot.style.setProperty('--pageButtonDefault', element('#palette_dashboard_8').value);
    dashRoot.style.setProperty('--pageButtonHover', element('#palette_dashboard_9').value);

    // utility
    dashRoot.style.setProperty('--statusOffline', element('#palette_status_offline').value);
    dashRoot.style.setProperty('--statusError', element('#palette_status_error').value);
    dashRoot.style.setProperty('--statusStarting', element('#palette_status_starting').value);
    dashRoot.style.setProperty('--statusOnline', element('#palette_status_online').value);

    // authentication
    authRoot.style.setProperty('--authA', element('#palette_auth_1').value);
    authRoot.style.setProperty('--authB', element('#palette_auth_2').value);
    authRoot.style.setProperty('--authC', element('#palette_auth_3').value);
    authRoot.style.setProperty('--authD', element('#palette_auth_4').value);
    authRoot.style.setProperty('--authE', element('#palette_auth_5').value);
    authRoot.style.setProperty('--authF', element('#palette_auth_6').value);
    authRoot.style.setProperty('--authG', element('#palette_auth_7').value);
    authRoot.style.setProperty('--authH', element('#palette_auth_8').value);
  }

  document.addEventListener("DOMContentLoaded", function () {
    element("#editor-form").addEventListener("change", () => {refreshPreview()});
  });

  function focusDash() { element(".preview-frame").contentDocument.getElementById('focus-dash').click() }
  function focusAuth() { element(".preview-frame").contentDocument.getElementById('focus-auth').click() }
</script>
