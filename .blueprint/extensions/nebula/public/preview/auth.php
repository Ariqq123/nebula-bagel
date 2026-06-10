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

<html <?php
  $auth_background_magic = $blueprint->dbGet('nebula', 'auth_background_magic');
  if($auth_background_magic != "") {
    echo('class="magic-pattern['.$auth_background_magic.']" view="auth"');
  }
?>>
  <head>
    <title>Auth Preview</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/extensions/nebula/libraries/authWatermark.css">
    <link rel="stylesheet" href="/extensions/nebula/libraries/patterns.css">
  </head>
  <body>
    <div class="input-mockup">
      <div class="mockup-container">
        <h2 class="title">Login to Continue</h2>
        <p>USERNAME OR EMAIL</p>
        <div class="input-box"></div>
        <p>PASSWORD</p>
        <div class="input-box"></div>
        <button class="input-btn">Login</button>
        <p class="link-center">FORGOT PASSWORD?</p>
      </div>
    </div>
    <?php
    if($blueprint->dbGet("settings", "recaptcha:enabled") == "true") { echo ('
    <!-- recaptcha -->
    <div class="notification">
      <div class="notificationBar"></div>
      <div class="notificationIcon"></div>
      <div class="notificationTextContainer">
        <p class="notificationText"><b style="font-size: 14px;">Protected by reCAPTCHA</b><br>
          <span style="font-size: 12px;"><a href="https://www.google.com/intl/en/policies/privacy/" style="color: #4D4DFF;">Privacy</a>, <a href="https://www.google.com/intl/en/policies/terms/" style="color: #4D4DFF;">Terms</a></span></p>
      </div>
    </div>
    '); }

    if($blueprint->dbGet("nebula", "watermark_auth") != "0") {
      echo ('<div class="nebula-watermark"><a class="nebula-watermark-text no-underline" href="https://nebula.style?utm_source=inlinenebula&utm_campaign=footer"><b class="watermark-highlight"><i class="bi bi-exclude"></i> Nebula</b> theme</a> by <a class="nebula-watermark-text" href="https://prpl.wtf?utm_source=inlinenebula&utm_campaign=footer" target="_blank">Emma (prpl.wtf)</a></div>');
    }
    ?>
  </body>

  <style>
    :root {
      --authA: <?php echo($blueprint->dbGet("nebula", "palette_auth_1")); ?>;
      --authB: <?php echo($blueprint->dbGet("nebula", "palette_auth_2")); ?>;
      --authC: <?php echo($blueprint->dbGet("nebula", "palette_auth_3")); ?>;
      --authD: <?php echo($blueprint->dbGet("nebula", "palette_auth_4")); ?>;
      --authE: <?php echo($blueprint->dbGet("nebula", "palette_auth_5")); ?>;
      --authF: <?php echo($blueprint->dbGet("nebula", "palette_auth_6")); ?>;
      --authG: <?php echo($blueprint->dbGet("nebula", "palette_auth_7")); ?>;
      --authH: <?php echo($blueprint->dbGet("nebula", "palette_auth_8")); ?>;
      --patternSizeAuth: <?php echo($blueprint->dbGet("nebula", "auth_background_magicsize").'px'); ?>;
    }
    * {
      transition: background-color .4s;
    }
    html {
      background: <?php
        if(($blueprint->dbGet("nebula", "auth_background_image") == "") && ($blueprint->dbGet("nebula", "auth_background_magic") == "")) {
          echo("var(--authA)");
        } else { echo("url('".$blueprint->dbGet("nebula", "auth_background_image")."') no-repeat"); }
      ?>;
      background-position: center;
      background-size: cover;
      height: 100%;
      width: 100%;
      overflow-y: hidden;
      overflow-x: hidden;
    }
    body {
      <?php if($blueprint->dbGet("nebula", "auth_background_appearance") == "1") { echo("backdrop-filter: blur(50px);"); } ?>
      <?php if($blueprint->dbGet("nebula", "auth_background_appearance") == "2") { echo("backdrop-filter: brightness(60%);"); } ?>
      height: 100%;
      width: 100%;
      margin: 0;
      color: #e5e8eb;
      font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    <?php
      if($blueprint->dbGet("nebula", "auth_customlogo") != "") {
        echo('
          .title {
            content: url("'.$blueprint->dbGet("nebula", "auth_customlogo").'");
            border-radius: 10px;
            height: 65px;
            padding-bottom: 0 !important;
            margin-left: auto;
            margin-right: auto;
          }
        ');
      }
    ?>

    .input-mockup {
      position: absolute;
      left: 50%;
      top: 50%;
      -webkit-transform: translate(-50%, -50%);
      transform: translate(-50%, -50%);
      background-color: var(--authB);
      max-height: 100%;
      width: 50%;
      border-radius: 17px;
      overflow-x: hidden;
      overflow-y: scroll;
    }
    @media (max-width: 745px) {
      .input-mockup {
        width: 60%;
      }
    }
    @media (max-width: 645px) {
      .input-mockup {
        width: 70%;
      }
    }
    @media (max-width: 545px) {
      .input-mockup {
        width: 80%;
      }
    }
    @media (max-width: 445px) {
      .input-mockup {
        width: 90%;
      }
    }
    @media (max-width: 345px) {
      .input-mockup {
        width: 100%;
      }
    }
    .mockup-container {
      padding: 15px;
      padding-left: 60px;
      padding-right: 60px;
    }
    .title {
      text-align: center;
      font-size: 1.875rem;
      font-family: 'IBM Plex Sans', sans-serif;
      padding-bottom: 10px;
    }
    p {
      color: var(--authG); /*#505050*/
      font-size: 14px;
    }

    .input-box {
      background-color: var(--authC);
      width: 100%;
      height: 50px;
      border-radius: 5px;
      border-bottom: 5px var(--authD) solid;
      margin-bottom: 25px;
    }

    .input-btn {
      background-color: var(--authF);
      color: var(--authH);
      border: none;
      width: 100%;
      height: 55px;
      border-radius: 100px;
      font-weight: 800;
    }

    .link-center {
      text-align: center;
      font-size: 15px;
      margin-top: 30px;
      margin-bottom: 20px;
      color: var(--authG);
    }

    *::-webkit-scrollbar {
      display: none;
    }
    * {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
  </style>



  <style>
    /* recaptcha */
    .notification {
      background-color: #0c090a;
      position: fixed;
      right: calc(-300px + 67.5px);
      bottom: 10px;
      width: 300px;
      height: 60px;
      border-radius: 6px 0px 0px 6px;
      transition: right .5s;
    }

    .notificationBar {
      float: left;
      background-color: #333333;
      width: 7.5px;
      height: 60px;
      border-radius: 6px 0px 0px 6px;
      transition:
        width .25s,
        border-radius .4s,
        background-color .6s;
    }

    .notification:hover .notificationBar {width: 10px; background-color: #3457D5;}
    .notification:hover .notificationIcon {rotate: 360deg;}
    .notification:hover {right: 0px;}

    .notificationIcon {
      background: url("https://i.imgur.com/LD3lZ9j.png") no-repeat;
      background-position: center;
      background-size: cover;
      width: 60px;
      height: 60px;
      scale: 0.6;
      float: left;
      transition: rotate 1.2s;
      transition-timing-function: cubic-bezier(0.1,1.2,1.5,1.0);
    }

    .notificationTextContainer {
      color: #fff !important;
      height: 60px;
      width: 230px;
      float: right;
    }

    .notificationText {
      font-family: "Inter", "Inter", monospace;
      color: #fff !important;
      padding: 0;
      margin-top: 11px;
    }
  </style>
</html>
