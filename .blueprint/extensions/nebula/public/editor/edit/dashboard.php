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
  <!-- patterns --> <link rel="stylesheet" href="/extensions/nebula/libraries/patterns.css">
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
        <button onclick="navigationAction('sidebar')" to="sidebar" class="navigation-button"><i class="bi bi-layout-sidebar-inset"></i></button>
        <button onclick="navigationAction('dashboard')" to="dashboard" class="navigation-button active"><i class="bi bi-grid-1x2"></i></button>
        <button onclick="navigationAction('authentication')" to="authentication" class="navigation-button"><i class="bi bi-box-arrow-in-right"></i></button>
        <button onclick="navigationAction('more')" to="more" class="navigation-button"><i class="bi bi-nut"></i></button>
        <div class="save-padding"></div>
        <button onclick="saveAction()" class="save-button"><i class="bi bi-floppy-fill"></i></button>
      </div>
      <div class="editor fade">
        <form action="/admin/extensions/nebula" method="POST" id="editor-form" autocomplete="off">
          <div class="editor-container">
            <h2 class="editor-title">Dashboard</h2>
            <p class="editor-description">Customize the Pterodactyl dashboard to your liking.</p>

            <!-- Background -->
            <div class="option">
              <button class="modal-open" onclick="backgroundModal()" type="button" style="float: left"><i class="bi bi-plus-lg"></i></button>
              <p class="option-title with-button">Background</p>
              <!-- Default -->
              <input type="radio" id="background-default" name="background_image" value="" class="hidden" <?php if($blueprint->dbGet("nebula", "background_image") == "" && $blueprint->dbGet("nebula", "background_magic") == "") { echo("checked=''"); } ?>/>
              <label for="background-default" class="option-radio sm aspect-square">
                <img src="../assets/images/authentication/background/default.png" loading="lazy"/>
              </label>
              <!-- Magic -->
              <input type="radio" id="background-magic" name="background_image" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") != "") { echo("checked=''"); } ?>/>
              <label for="background-magic" class="option-radio sm aspect-square" value="" onclick="event.preventDefault();modal('#magic-modal');">
                <img src="../assets/images/dashboard/background/magic.png" loading="lazy"/>
              </label>
              <!-- Image -->
              <input type="radio" id="background-image" name="background_image" class="hidden" <?php if($blueprint->dbGet("nebula", "background_image") != "") { echo("checked=''"); } ?>/>
              <label for="background-image" class="option-radio sm aspect-square" onclick="event.preventDefault();modal('#background-modal');">
                <img src="../assets/images/dashboard/background/image.png" loading="lazy"/>
              </label>
              <p class="option-footer">Customize the background image of your Pterodactyl panel.</p>
            </div>

            <!-- Server list -->
            <div class="option">
              <button class="modal-open" onclick="modal('#serverlist-modal')" type="button" style="float: left"><i class="bi bi-plus-lg"></i></button>
              <p class="option-title with-button">Server list</p>
              <!-- Cards -->
              <input type="radio" id="serverlist-cards" name="server_list" value="cards" class="hidden" <?php if($blueprint->dbGet("nebula", "server_list") == "cards") { echo("checked=''"); } ?>>
              <label for="serverlist-cards" class="option-radio">
                <img src="../assets/images/dashboard/serverlist/cards.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- List -->
              <input type="radio" id="serverlist-list" name="server_list" value="list" class="hidden" <?php if($blueprint->dbGet("nebula", "server_list") == "list") { echo("checked=''"); } ?>>
              <label for="serverlist-list" class="option-radio">
                <img src="../assets/images/dashboard/serverlist/list.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Configure how to display servers in the server list.</p>
            </div>

            <!-- Animations -->
            <div class="option">
              <p class="option-title">Animations</p>
              <!-- Fade up -->
              <input type="radio" id="animations-fadeup" name="animations" value="fadeup" class="hidden" <?php if($blueprint->dbGet("nebula", "animations") == "fadeup") { echo("checked=''"); } ?>>
              <label for="animations-fadeup" class="option-radio">
                <img src="../assets/images/dashboard/animations/fadeup.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Zoom out -->
              <input type="radio" id="animations-zoomout" name="animations" value="zoomout" class="hidden" <?php if($blueprint->dbGet("nebula", "animations") == "zoomout") { echo("checked=''"); } ?>>
              <label for="animations-zoomout" class="option-radio">
                <img src="../assets/images/dashboard/animations/zoomout.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Fade in -->
              <input type="radio" id="animations-fadein" name="animations" value="fadein" class="hidden" <?php if($blueprint->dbGet("nebula", "animations") == "fadein") { echo("checked=''"); } ?>>
              <label for="animations-fadein" class="option-radio">
                <img src="../assets/images/dashboard/animations/fadein.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="animations-disabled" name="animations" value="disabled" class="hidden" <?php if($blueprint->dbGet("nebula", "animations") == "disabled") { echo("checked=''"); } ?>>
              <label for="animations-disabled" class="option-radio">
                <img src="../assets/images/dashboard/animations/disabled.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Choose how to animate elements on the panel.</p>
            </div>

            <!-- Keybinds -->
            <div class="option">
              <button class="modal-open" onclick="modal('#keybinds-modal')" type="button" style="float: left"><i class="bi bi-plus-lg"></i></button>
              <p class="option-title with-button">Keybinds</p>
              <!-- Enabled -->
              <input type="radio" id="keybinds-on" name="keyboard_shortcuts" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "keyboard_shortcuts") == "1") { echo("checked=''"); } ?>>
              <label for="keybinds-on" class="option-radio">
                <img src="../assets/images/dashboard/keybinds/on.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="keybinds-off" name="keyboard_shortcuts" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "keyboard_shortcuts") == "0") { echo("checked=''"); } ?>>
              <label for="keybinds-off" class="option-radio">
                <img src="../assets/images/dashboard/keybinds/off.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Enable or disable the keybinds feature in Nebula.</p>
            </div>

            <!-- Graphs -->
            <div class="option">
              <p class="option-title">Graphs</p>
              <!-- Enabled -->
              <input type="radio" id="graphs-on" name="server_overview_graphs" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "server_overview_graphs") == "1") { echo("checked=''"); } ?>>
              <label for="graphs-on" class="option-radio">
                <img src="../assets/images/dashboard/graphs/on.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="graphs-off" name="server_overview_graphs" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "server_overview_graphs") == "0") { echo("checked=''"); } ?>>
              <label for="graphs-off" class="option-radio">
                <img src="../assets/images/dashboard/graphs/off.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Choose whether to display or hide the resource graphs on server management pages.</p>
            </div>

            <!-- Power button style -->
            <div class="option">
              <p class="option-title">Power button style</p>
              <!-- Enabled -->
              <input type="radio" id="coloredbtn-on" name="server_colored_power" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "server_colored_power") == "1") { echo("checked=''"); } ?>>
              <label for="coloredbtn-on" class="option-radio">
                <img src="../assets/images/dashboard/coloredbtn/on.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="coloredbtn-off" name="server_colored_power" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "server_colored_power") == "0") { echo("checked=''"); } ?>>
              <label for="coloredbtn-off" class="option-radio">
                <img src="../assets/images/dashboard/coloredbtn/off.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Configure how the power buttons look.</p>
            </div>

            <!-- Watermark -->
            <div class="option">
              <p class="option-title">Watermark</p>
              <!-- Enabled -->
              <input type="radio" id="watermark-on" name="watermark" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "watermark") == "1") { echo("checked=''"); } ?>>
              <label for="watermark-on" class="option-radio">
                <img src="../assets/images/dashboard/watermark/on.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="watermark-off" name="watermark" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "watermark") == "0") { echo("checked=''"); } ?>>
              <label for="watermark-off" class="option-radio">
                <img src="../assets/images/dashboard/watermark/off.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Choose to show or hide the Nebula footer on the Pterodactyl dashboard.</p>
            </div>

            <!-- Border radius -->
            <div class="option">
              <p class="option-title">Border radius</p>
              <div class="option-container">
                <input type="range" id="border-radius" min="0" max="20" name="border_radius" value="<?php echo $blueprint->dbGet("nebula", "border_radius"); ?>" step="1" class="option-slider">
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
              <p class="option-footer">Adjust the border radius of certain dashboard elements.</p>
            </div>
          </div>

          <!-- Background modal -->
          <div class="editor-modal" id="background-modal">
            <button class="modal-close" onclick="closeModal('#background-modal')" type="button" style="float: left"><i class="bi bi-chevron-left"></i></button>
            <h2 class="editor-title with-button">Background image</h2>
            <p class="editor-description">Change your Pterodactyl panel's background.</p>

            <!-- Background image -->
            <div class="option">
              <p class="option-title">Background image</p>
              <div class="option-container with-margin">
                <span class="option-icon"><i class="bi bi-image"></i></span>
                <input type="text" id="background-image-field" name="background_image" class="option-input with-icon" placeholder="https://nebula.style/wallpaper.jpg" value="<?php echo $blueprint->dbGet("nebula", "background_image"); ?>">
              </div>
              <p class="option-footer">Use a custom background image for the Pterodactyl panel.</p>
            </div>

            <!-- Filters -->
            <div class="option">
              <p class="option-title">Filters</p>
              <!-- Disabled -->
              <input type="radio" id="filter-disabled" name="background_appearance" value="0" class="hidden"/>
              <!-- None -->
              <input type="radio" id="filter-none" name="background_appearance" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "background_appearance") == "0") { echo("checked=''"); } ?>/>
              <label for="filter-none" class="option-radio" onclick="bgDepend(event)">
                <img src="../assets/images/dashboard/filters/none.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <script> tippy('label[for="filter-none"] img', { content: "None", arrow: false, animation: 'shift-away' }); </script>
              <!-- Blur -->
              <input type="radio" id="filter-blur" name="background_appearance" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "background_appearance") == "1") { echo("checked=''"); } ?>/>
              <label for="filter-blur" class="option-radio" onclick="bgDepend(event)">
                <img src="../assets/images/dashboard/filters/blur.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <script> tippy('label[for="filter-blur"] img', { content: "Blur", arrow: false, animation: 'shift-away' }); </script>
              <!-- Blur -->
              <input type="radio" id="filter-dim" name="background_appearance" value="2" class="hidden" <?php if($blueprint->dbGet("nebula", "background_appearance") == "2") { echo("checked=''"); } ?>/>
              <label for="filter-dim" class="option-radio" onclick="bgDepend(event)">
                <img src="../assets/images/dashboard/filters/dim.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <script> tippy('label[for="filter-dim"] img', { content: "Dim", arrow: false, animation: 'shift-away' }); </script>
              <p class="option-footer">Change the appearance of your custom background image.</p>
            </div>

            <!-- Transparency -->
            <div class="option">
              <p class="option-title">Transparency</p>
              <!-- Disabled -->
              <input type="radio" id="transparency-disabled" name="dashboard_transparency" value="0" class="hidden"/>
              <!-- None -->
              <input type="radio" id="transparency-none" name="dashboard_transparency" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "dashboard_transparency") == "0") { echo("checked=''"); } ?>/>
              <label for="transparency-none" class="option-radio" onclick="bgDepend(event)">
                <img src="../assets/images/dashboard/transparency/none.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <script> tippy('label[for="transparency-none"] img', { content: "None", arrow: false, animation: 'shift-away' }); </script>
              <!-- Subtle -->
              <input type="radio" id="transparency-subtle" name="dashboard_transparency" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "dashboard_transparency") == "1") { echo("checked=''"); } ?>/>
              <label for="transparency-subtle" class="option-radio" onclick="bgDepend(event)">
                <img src="../assets/images/dashboard/transparency/subtle.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <script> tippy('label[for="transparency-subtle"] img', { content: "Subtle", arrow: false, animation: 'shift-away' }); </script>
              <!-- Transparent -->
              <input type="radio" id="transparency-transparent" name="dashboard_transparency" value="2" class="hidden" <?php if($blueprint->dbGet("nebula", "dashboard_transparency") == "2") { echo("checked=''"); } ?>/>
              <label for="transparency-transparent" class="option-radio" onclick="bgDepend(event)">
                <img src="../assets/images/dashboard/transparency/transparent.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <script> tippy('label[for="transparency-transparent"] img', { content: "Transparent", arrow: false, animation: 'shift-away' }); </script>
              <!-- Max -->
              <input type="radio" id="transparency-max" name="dashboard_transparency" value="3" class="hidden" <?php if($blueprint->dbGet("nebula", "dashboard_transparency") == "3") { echo("checked=''"); } ?>/>
              <label for="transparency-max" class="option-radio" onclick="bgDepend(event)">
                <img src="../assets/images/dashboard/transparency/max.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <script> tippy('label[for="transparency-max"] img', { content: "Max", arrow: false, animation: 'shift-away' }); </script>
              <p class="option-footer">Customize transparency on dashboard elements when using custom backgrounds.</p>
            </div>
          </div>

          <!-- Magic background modal -->
          <div class="editor-modal" id="magic-modal">
            <button class="modal-close" onclick="closeModal('#magic-modal')" type="button" style="float: left"><i class="bi bi-chevron-left"></i></button>
            <h2 class="editor-title with-button">Background pattern</h2>
            <p class="editor-description">Use a pattern as your Pterodactyl panel's background.</p>

            <!-- Pattern -->
            <div class="option">
              <p class="option-title">Pattern</p>
              <!-- Disabled -->
              <input type="radio" id="magic-disabled" name="background_magic" value="" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "") { echo("checked=''"); } ?>/>
              <!-- Tiles -->
              <input type="radio" id="magic-tiles" name="background_magic" value="tiles" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "tiles") { echo("checked=''"); } ?>/>
              <label for="magic-tiles" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview tiles" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Cubes -->
              <input type="radio" id="magic-cubes" name="background_magic" value="cubes" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "cubes") { echo("checked=''"); } ?>/>
              <label for="magic-cubes" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview cubes" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Rotated squares -->
              <input type="radio" id="magic-rotated-squares" name="background_magic" value="rotated-squares" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "rotated-squares") { echo("checked=''"); } ?>/>
              <label for="magic-rotated-squares" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview rotated-squares" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- L-shape -->
              <input type="radio" id="magic-l-shape" name="background_magic" value="l-shape" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "l-shape") { echo("checked=''"); } ?>/>
              <label for="magic-l-shape" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview l-shape" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Zig-zag -->
              <input type="radio" id="magic-zig-zag" name="background_magic" value="zig-zag" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "zig-zag") { echo("checked=''"); } ?>/>
              <label for="magic-zig-zag" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview zig-zag" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Wavy checkerboard -->
              <input type="radio" id="magic-wavy-checkerboard" name="background_magic" value="wavy-checkerboard" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "wavy-checkerboard") { echo("checked=''"); } ?>/>
              <label for="magic-wavy-checkerboard" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview wavy-checkerboard" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Chevrons -->
              <input type="radio" id="magic-chevrons" name="background_magic" value="chevrons" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "chevrons") { echo("checked=''"); } ?>/>
              <label for="magic-chevrons" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview chevrons" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Houndstooth -->
              <input type="radio" id="magic-houndstooth" name="background_magic" value="houndstooth" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "houndstooth") { echo("checked=''"); } ?>/>
              <label for="magic-houndstooth" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview houndstooth" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Quarter circles -->
              <input type="radio" id="magic-quarter-circles" name="background_magic" value="quarter-circles" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "quarter-circles") { echo("checked=''"); } ?>/>
              <label for="magic-quarter-circles" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview quarter-circles" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Diagonal rectangles -->
              <input type="radio" id="magic-diagonal-rectangles" name="background_magic" value="diagonal-rectangles" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "diagonal-rectangles") { echo("checked=''"); } ?>/>
              <label for="magic-diagonal-rectangles" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview diagonal-rectangles" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Alternating arc -->
              <input type="radio" id="magic-alternating-arc" name="background_magic" value="alternating-arc" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "alternating-arc") { echo("checked=''"); } ?>/>
              <label for="magic-alternating-arc" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview alternating-arc" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Rotated rectangles -->
              <input type="radio" id="magic-rotated-rectangles" name="background_magic" value="rotated-rectangles" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "rotated-rectangles") { echo("checked=''"); } ?>/>
              <label for="magic-rotated-rectangles" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview rotated-rectangles" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Concentric arrows -->
              <input type="radio" id="magic-concentric-arrows" name="background_magic" value="concentric-arrows" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "concentric-arrows") { echo("checked=''"); } ?>/>
              <label for="magic-concentric-arrows" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview concentric-arrows" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Outline triangles -->
              <input type="radio" id="magic-outline-triangles" name="background_magic" value="outline-triangles" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "outline-triangles") { echo("checked=''"); } ?>/>
              <label for="magic-outline-triangles" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview outline-triangles" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Moon -->
              <input type="radio" id="magic-moon" name="background_magic" value="moon" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "moon") { echo("checked=''"); } ?>/>
              <label for="magic-moon" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview moon" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Polka -->
              <input type="radio" id="magic-polka" name="background_magic" value="polka" class="hidden" <?php if($blueprint->dbGet("nebula", "background_magic") == "polka") { echo("checked=''"); } ?>/>
              <label for="magic-polka" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview polka" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <p class="option-footer">Choose a pattern you'd like to use as your background image.</p>
            </div>

            <!-- Size -->
            <div class="option">
              <p class="option-title">Size</p>
              <div class="option-container">
                <input type="range" id="pattern-size" min="50" max="500" name="background_magicsize" value="<?php echo $blueprint->dbGet("nebula", "background_magicsize"); ?>" step="5" class="option-slider">
                <script>
                  document.addEventListener('DOMContentLoaded', () => {
                    const slider = document.querySelector('.option-container #pattern-size');
                    const tip = tippy(slider, {
                      content: Math.floor(slider.value) + 'px',
                      trigger: 'manual',
                      arrow: false,
                      animation: 'shift-away'
                    });

                    slider.addEventListener('input', () => {
                      tip.setContent(`${Math.floor(slider.value) + 'px'}`);
                      tip.show();
                    });

                    slider.addEventListener('blur', () => {
                      tip.hide();
                    });
                  });
                </script>
              </div>
              <p class="option-footer">Adjust how large you want your selected pattern to be.</p>
            </div>
          </div>

          <!-- Keybinds modal -->
          <div class="editor-modal" id="keybinds-modal">
            <button class="modal-close" onclick="closeModal('#keybinds-modal')" type="button" style="float: left"><i class="bi bi-chevron-left"></i></button>
            <h2 class="editor-title with-button">Keybinds</h2>
            <p class="editor-description">Additional options related to the keybinds feature.</p>

            <!-- Display mode -->
            <div class="option">
              <p class="option-title">Display mode</p>
              <!-- Prefer icons -->
              <input type="radio" id="displaymode-prefer-icons" name="keybind_icons" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "keybind_icons") == "1") { echo("checked=''"); } ?>>
              <label for="displaymode-prefer-icons" class="option-radio sm aspect-square">
                <img src="../assets/images/dashboard/keybinds/prefer-icons.png" loading="lazy"/>
              </label>
              <!-- Text-only -->
              <input type="radio" id="displaymode-text-only" name="keybind_icons" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "keybind_icons") == "0") { echo("checked=''"); } ?>>
              <label for="displaymode-text-only" class="option-radio sm aspect-square">
                <img src="../assets/images/dashboard/keybinds/text-only.png" loading="lazy"/>
              </label>
              <p class="option-footer">Choose in which way to display keyboard shortcut names.</p>
            </div>
          </div>

          <!-- Serverlist modal -->
          <div class="editor-modal" id="serverlist-modal">
            <button class="modal-close" onclick="closeModal('#serverlist-modal')" type="button" style="float: left"><i class="bi bi-chevron-left"></i></button>
            <h2 class="editor-title with-button">Server list</h2>
            <p class="editor-description">Additional options related to the server list.</p>

            <!-- Status gradient -->
            <div class="option">
              <p class="option-title">Status gradient</p>
              <!-- Default -->
              <input type="radio" id="statusgradient-default" name="statusgradient_style" value="default" class="hidden" <?php if($blueprint->dbGet("nebula", "statusgradient_style") == "default") { echo("checked=''"); } ?>>
              <label for="statusgradient-default" class="option-radio">
                <img src="../assets/images/dashboard/statusgradient/default.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Flat -->
              <input type="radio" id="statusgradient-flat" name="statusgradient_style" value="flat" class="hidden" <?php if($blueprint->dbGet("nebula", "statusgradient_style") == "flat") { echo("checked=''"); } ?>>
              <label for="statusgradient-flat" class="option-radio">
                <img src="../assets/images/dashboard/statusgradient/flat.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Choose to enable or disable the status gradient.</p>
            </div>
          </div>

          <div id="editor-submit">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="_endpoint" value="/extensions/nebula/editor/edit/dashboard.php">
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
    <div class="source-overlay" style="
      position: fixed;
      right: 0;
      top: 0;
      z-index: 10;
      background-color: #050404;
      width: 100%;
      height: 100%;
      transition: width 1s;
      display: none;
    "></div>
  </body>
</html>

<script>
  const params = new URL(window.location).searchParams;
  if(params.get('source') == "panel" && params.get('animate') == "true") {
    element(".source-overlay").style.display = "block";
    setTimeout(() => {
      element(".source-overlay").style.width = "0%";
    }, 25);
    setTimeout(() => {
      element(".source-overlay").style.display = "none"
    }, 1025);
  }
  if(params.get('location') != undefined) {
    element(".preview-frame").src = params.get('location')
  }

  document.addEventListener("DOMContentLoaded", function (event) {
    // default background
    document.getElementById("background-default").addEventListener("click", function () {
      document.getElementById("background-image-field").value = null;
      document.getElementById("magic-disabled").checked = true;
      document.getElementById("filter-disabled").click()
      document.getElementById("transparency-disabled").click()
    });
    // image background
    document.getElementById("background-image-field").addEventListener("change", function () {
      let elem = document.getElementById("background-image-field")
      if(elem.value == "") {
        document.getElementById("filter-disabled").checked = true
        document.getElementById("transparency-disabled").checked = true
        document.getElementById("background-default").checked = true
      } else {
        document.getElementById("background-image").click()
        document.getElementById("magic-disabled").checked = true;
        if(document.getElementById("filter-disabled").checked) {
          document.getElementById("filter-none").checked = true
        }
        if(document.getElementById("transparency-disabled").checked) {
          document.getElementById("transparency-none").checked = true
        }
      }
    });

    let elem = document.getElementById("background-image-field")
    if(elem.value == "") {
      document.getElementById("filter-disabled").checked = true
      document.getElementById("transparency-disabled").checked = true
    }
  });
  function bgDepend(event) {
    if(document.getElementById('background-image-field').value == '') {
      event.preventDefault()
      document.getElementById('background-image-field').focus()
    }
  }
  // magic background
  function magicBackground() {
    document.getElementById("background-image-field").value = null;
    document.getElementById("filter-disabled").click()
    document.getElementById("transparency-disabled").click()
    document.getElementById("background-magic").checked = true;
  }
  // background button
  function backgroundModal() {
    if(element("#background-image-field").value == "") {
      modal('#magic-modal')
    } else {
      modal('#background-modal')
    }
  }
</script>
