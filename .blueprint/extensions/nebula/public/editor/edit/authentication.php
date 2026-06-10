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
<html style="background-color: #050404">
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

  <body>
    <div class="container">
    <div class="navigation">
        <button onclick="navigationAction('admin')" to="admin" class="return-button"><i class="bi bi-arrow-90deg-left"></i></button>
        <div style="height: 80px;"></div>
        <button onclick="navigationAction('general')" to="general" class="navigation-button"><i class="bi bi-sliders2"></i></button>
        <button onclick="navigationAction('palette')" to="palette" class="navigation-button"><i class="bi bi-palette2"></i></button>
        <button onclick="navigationAction('sidebar')" to="sidebar" class="navigation-button"><i class="bi bi-layout-sidebar-inset"></i></button>
        <button onclick="navigationAction('dashboard')" to="dashboard" class="navigation-button"><i class="bi bi-grid-1x2"></i></button>
        <button onclick="navigationAction('authentication')" to="authentication" class="navigation-button active"><i class="bi bi-box-arrow-in-right"></i></button>
        <button onclick="navigationAction('more')" to="more" class="navigation-button"><i class="bi bi-nut"></i></button>
        <div class="save-padding"></div>
        <button onclick="saveAction()" class="save-button"><i class="bi bi-floppy-fill"></i></button>
      </div>
      <div class="editor fade">
        <form action="/admin/extensions/nebula" method="POST" id="editor-form" autocomplete="off">
          <div class="editor-container">
            <h2 class="editor-title">Authentication</h2>
            <p class="editor-description">Change the look of your authentication page.</p>

            <!-- Background -->
            <div class="option">
              <button class="modal-open" onclick="backgroundModal()" type="button" style="float: left"><i class="bi bi-plus-lg"></i></button>
              <p class="option-title with-button">Background</p>
              <!-- Default -->
              <input type="radio" id="background-default" name="auth_background_image" value="" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_image") == "" && $blueprint->dbGet("nebula", "auth_background_magic") == "") { echo("checked=''"); } ?>/>
              <label for="background-default" class="option-radio sm aspect-square">
                <img src="../assets/images/authentication/background/default.png" loading="lazy"/>
              </label>
              <!-- Magic -->
              <input type="radio" id="background-magic" name="auth_background_image" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") != "") { echo("checked=''"); } ?>/>
              <label for="background-magic" class="option-radio sm aspect-square" value="" onclick="event.preventDefault();modal('#magic-modal');">
                <img src="../assets/images/authentication/background/magic.png" loading="lazy"/>
              </label>
              <!-- Image -->
              <input type="radio" id="background-image" name="auth_background_image" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_image") != "") { echo("checked=''"); } ?>/>
              <label for="background-image" class="option-radio sm aspect-square" onclick="event.preventDefault();modal('#background-modal');">
                <img src="../assets/images/authentication/background/image.png" loading="lazy"/>
              </label>
              <p class="option-footer">Customize the background image of your authentication page.</p>
            </div>

            <!-- Custom logo -->
            <div class="option" id="option-customlogo">
              <p class="option-title">Custom logo</p>
              <div class="option-container with-margin" onclick="element('#type-wide').click()">
                <span class="option-icon"><i class="bi bi-image"></i></span>
                <input type="text" id="custom-logo-field" name="auth_customlogo" class="option-input with-icon" placeholder="https://nebula.style/logo.jpg" value="<?php echo $blueprint->dbGet("nebula", "auth_customlogo"); ?>">
              </div>
              <p class="option-footer">Add your own logo to your authentication page.</p>
            </div>

            <!-- Watermark -->
            <div class="option">
              <p class="option-title">Watermark</p>
              <!-- Enabled -->
              <input type="radio" id="watermark-on" name="watermark_auth" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "watermark_auth") == "1") { echo("checked=''"); } ?>>
              <label for="watermark-on" class="option-radio">
                <img src="../assets/images/authentication/watermark/on.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <!-- Disabled -->
              <input type="radio" id="watermark-off" name="watermark_auth" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "watermark_auth") == "0") { echo("checked=''"); } ?>>
              <label for="watermark-off" class="option-radio">
                <img src="../assets/images/authentication/watermark/off.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <p class="option-footer">Choose to show or hide the Nebula watermark on your authentication page.</p>
            </div>
          </div>

          <!-- Background modal -->
          <div class="editor-modal" id="background-modal">
            <button class="modal-close" onclick="closeModal('#background-modal')" type="button" style="float: left"><i class="bi bi-chevron-left"></i></button>
            <h2 class="editor-title with-button">Background image</h2>
            <p class="editor-description">Change your authentication page's background.</p>

            <!-- Background image -->
            <div class="option">
              <p class="option-title">Background image</p>
              <div class="option-container with-margin">
                <span class="option-icon"><i class="bi bi-image"></i></span>
                <input type="text" id="background-image-field" name="auth_background_image" class="option-input with-icon" placeholder="https://nebula.style/wallpaper.jpg" value="<?php echo $blueprint->dbGet("nebula", "auth_background_image"); ?>">
              </div>
              <p class="option-footer">Use a custom background image for the authentication page.</p>
            </div>

            <!-- Filters -->
            <div class="option">
              <p class="option-title">Filters</p>
              <!-- Disabled -->
              <input type="radio" id="filter-disabled" name="auth_background_appearance" value="0" class="hidden"/>
              <!-- None -->
              <input type="radio" id="filter-none" name="auth_background_appearance" value="0" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_appearance") == "0") { echo("checked=''"); } ?>/>
              <label for="filter-none" class="option-radio" onclick="bgDepend(event)">
                <img src="../assets/images/authentication/filters/none.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <script> tippy('label[for="filter-none"] img', { content: "None", arrow: false, animation: 'shift-away' }); </script>
              <!-- Blur -->
              <input type="radio" id="filter-blur" name="auth_background_appearance" value="1" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_appearance") == "1") { echo("checked=''"); } ?>/>
              <label for="filter-blur" class="option-radio" onclick="bgDepend(event)">
                <img src="../assets/images/authentication/filters/blur.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <script> tippy('label[for="filter-blur"] img', { content: "Blur", arrow: false, animation: 'shift-away' }); </script>
              <!-- Blur -->
              <input type="radio" id="filter-dim" name="auth_background_appearance" value="2" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_appearance") == "2") { echo("checked=''"); } ?>/>
              <label for="filter-dim" class="option-radio" onclick="bgDepend(event)">
                <img src="../assets/images/authentication/filters/dim.png" loading="lazy" class="aspect-16:9"/>
              </label>
              <script> tippy('label[for="filter-dim"] img', { content: "Dim", arrow: false, animation: 'shift-away' }); </script>
              <p class="option-footer">Change the appearance of your custom background image.</p>
            </div>
          </div>

          <!-- Magic background modal -->
          <div class="editor-modal" id="magic-modal">
            <button class="modal-close" onclick="closeModal('#magic-modal')" type="button" style="float: left"><i class="bi bi-chevron-left"></i></button>
            <h2 class="editor-title with-button">Background pattern</h2>
            <p class="editor-description">Use a pattern as your authentication page's background.</p>

            <!-- Pattern -->
            <div class="option">
              <p class="option-title">Pattern</p>
              <!-- Disabled -->
              <input type="radio" id="magic-disabled" name="auth_background_magic" value="" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "") { echo("checked=''"); } ?>/>
              <!-- Tiles -->
              <input type="radio" id="magic-tiles" name="auth_background_magic" value="tiles" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "tiles") { echo("checked=''"); } ?>/>
              <label for="magic-tiles" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview tiles" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Cubes -->
              <input type="radio" id="magic-cubes" name="auth_background_magic" value="cubes" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "cubes") { echo("checked=''"); } ?>/>
              <label for="magic-cubes" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview cubes" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Rotated squares -->
              <input type="radio" id="magic-rotated-squares" name="auth_background_magic" value="rotated-squares" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "rotated-squares") { echo("checked=''"); } ?>/>
              <label for="magic-rotated-squares" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview rotated-squares" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- L-shape -->
              <input type="radio" id="magic-l-shape" name="auth_background_magic" value="l-shape" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "l-shape") { echo("checked=''"); } ?>/>
              <label for="magic-l-shape" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview l-shape" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Zig-zag -->
              <input type="radio" id="magic-zig-zag" name="auth_background_magic" value="zig-zag" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "zig-zag") { echo("checked=''"); } ?>/>
              <label for="magic-zig-zag" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview zig-zag" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Wavy checkerboard -->
              <input type="radio" id="magic-wavy-checkerboard" name="auth_background_magic" value="wavy-checkerboard" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "wavy-checkerboard") { echo("checked=''"); } ?>/>
              <label for="magic-wavy-checkerboard" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview wavy-checkerboard" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Chevrons -->
              <input type="radio" id="magic-chevrons" name="auth_background_magic" value="chevrons" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "chevrons") { echo("checked=''"); } ?>/>
              <label for="magic-chevrons" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview chevrons" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Houndstooth -->
              <input type="radio" id="magic-houndstooth" name="auth_background_magic" value="houndstooth" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "houndstooth") { echo("checked=''"); } ?>/>
              <label for="magic-houndstooth" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview houndstooth" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Quarter circles -->
              <input type="radio" id="magic-quarter-circles" name="auth_background_magic" value="quarter-circles" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "quarter-circles") { echo("checked=''"); } ?>/>
              <label for="magic-quarter-circles" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview quarter-circles" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Diagonal rectangles -->
              <input type="radio" id="magic-diagonal-rectangles" name="auth_background_magic" value="diagonal-rectangles" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "diagonal-rectangles") { echo("checked=''"); } ?>/>
              <label for="magic-diagonal-rectangles" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview diagonal-rectangles" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Alternating arc -->
              <input type="radio" id="magic-alternating-arc" name="auth_background_magic" value="alternating-arc" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "alternating-arc") { echo("checked=''"); } ?>/>
              <label for="magic-alternating-arc" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview alternating-arc" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Rotated rectangles -->
              <input type="radio" id="magic-rotated-rectangles" name="auth_background_magic" value="rotated-rectangles" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "rotated-rectangles") { echo("checked=''"); } ?>/>
              <label for="magic-rotated-rectangles" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview rotated-rectangles" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Concentric arrows -->
              <input type="radio" id="magic-concentric-arrows" name="auth_background_magic" value="concentric-arrows" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "concentric-arrows") { echo("checked=''"); } ?>/>
              <label for="magic-concentric-arrows" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview concentric-arrows" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Outline triangles -->
              <input type="radio" id="magic-outline-triangles" name="auth_background_magic" value="outline-triangles" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "outline-triangles") { echo("checked=''"); } ?>/>
              <label for="magic-outline-triangles" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview outline-triangles" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Moon -->
              <input type="radio" id="magic-moon" name="auth_background_magic" value="moon" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "moon") { echo("checked=''"); } ?>/>
              <label for="magic-moon" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview moon" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <!-- Polka -->
              <input type="radio" id="magic-polka" name="auth_background_magic" value="polka" class="hidden" <?php if($blueprint->dbGet("nebula", "auth_background_magic") == "polka") { echo("checked=''"); } ?>/>
              <label for="magic-polka" onclick="magicBackground()" class="option-radio sm aspect-square pattern-label">
                <img class="pattern-preview polka" src="/extensions/nebula/libraries/assets/transparent.png" loading="lazy"/>
              </label>
              <p class="option-footer">Choose a pattern you'd like to use as your background image.</p>
            </div>

            <!-- Size -->
            <div class="option">
              <p class="option-title">Size</p>
              <div class="option-container">
                <input type="range" id="pattern-size" min="50" max="500" name="auth_background_magicsize" value="<?php echo $blueprint->dbGet("nebula", "auth_background_magicsize"); ?>" step="5" class="option-slider">
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

          <div id="editor-submit">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="_endpoint" value="/extensions/nebula/editor/edit/authentication.php">
            <input type="hidden" name="_method" value="PATCH">
            <button type="submit" class="hidden" id="submit"></button>
          </div>
        </form>
      </div>
      <div class="preview fade">
        <iframe
          title="Preview"
          class="preview-frame match-auth-bg"
          src="/extensions/nebula/preview/auth.php"
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
    // default background
    document.getElementById("background-default").addEventListener("click", function () {
      document.getElementById("background-image-field").value = null;
      document.getElementById("magic-disabled").checked = true;
      document.getElementById("filter-disabled").click()
    });
    // image background
    document.getElementById("background-image-field").addEventListener("change", function () {
      let elem = document.getElementById("background-image-field")
      if(elem.value == "") {
        document.getElementById("filter-disabled").checked = true
        document.getElementById("background-default").checked = true
      } else {
        document.getElementById("background-image").click()
        document.getElementById("magic-disabled").checked = true;
        if(document.getElementById("filter-disabled").checked) {
          document.getElementById("filter-none").checked = true
        }
      }
    });

    let elem = document.getElementById("background-image-field")
    if(elem.value == "") {
      document.getElementById("filter-disabled").checked = true
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
