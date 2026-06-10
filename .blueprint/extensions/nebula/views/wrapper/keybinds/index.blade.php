<!-- KEYBINDS MODAL -->
<?php
  if($n_keybind_icons == "0") {
    $KEY_CTRL = 'CTRL';
    $KEY_ALT = 'ALT';
    $KEY_CMD = 'CMD';
    $KEY_WIN = 'WIN';
    $KEY_SHIFT = 'SHIFT';
    $KEY_ESCAPE = 'ESC';
    $KEY_SLASH = '/';

    $KEY_1 = "1";
    $KEY_2 = "2";
    $KEY_3 = "3";
    $KEY_4 = "4";
    $KEY_5 = "5";
    $KEY_6 = "6";
    $KEY_7 = "7";
    $KEY_8 = "8";
    $KEY_9 = "9";
    $KEY_0 = "0";
  } else {
    $KEY_CTRL = '<i class="bi bi-option"></i>';
    $KEY_ALT = '<i class="bi bi-alt"></i>';
    $KEY_CMD = '<i class="bi bi-command"></i>';
    $KEY_WIN = '<i class="bi bi-microsoft"></i>';
    $KEY_SHIFT = '<i class="bi bi-shift"></i>';
    $KEY_ESCAPE = '<i class="bi bi-escape"></i>';
    $KEY_SLASH = '<i class="bi bi-slash-lg"></i>';

    $KEY_1 = '<i class="bi bi-1-square"></i>';
    $KEY_2 = '<i class="bi bi-2-square"></i>';
    $KEY_3 = '<i class="bi bi-3-square"></i>';
    $KEY_4 = '<i class="bi bi-4-square"></i>';
    $KEY_5 = '<i class="bi bi-5-square"></i>';
    $KEY_6 = '<i class="bi bi-6-square"></i>';
    $KEY_7 = '<i class="bi bi-7-square"></i>';
    $KEY_8 = '<i class="bi bi-8-square"></i>';
    $KEY_9 = '<i class="bi bi-9-square"></i>';
    $KEY_0 = '<i class="bi bi-0-square"></i>';
  };
?>

  <a onclick="hideKeybinds()">
    <div id="keybindsSiteOverlay" class="keybinds-site-overlay"></div>
  </a>
  <div id="keybindsContainer" class="keybinds-container">
    <div class="keybinds-content-box">
      <div class="keybinds-content">
        <h2 class="keybinds-content-title">Keybinds</h2>
        <p class="keybinds-content-text">Quickly navigate through the panel using keybinds.</p><br>
        
        @include('blueprint.extensions.nebula.wrapper.keybinds.binds')
        @include('blueprint.extensions.nebula.wrapper.keybinds.keys')

      </div>
    </div>
    <div class="keybinds-controls">
      <a onclick="hideKeybinds()">
        <button class="keybinds-controls-button" role="button">Close</button>
      </a>
    </div>
  </div>
  <div id="keybindsHideAlert" class="keybinds-close-alert">
    <p>Use <code style="background-color: var(--pageSecondaryHover); padding: 5px 10px; border-radius: 5px;">{!! $KEY_CTRL !!} {!! $KEY_SLASH !!}</code> to show these keybinds again.</p>
  </div>