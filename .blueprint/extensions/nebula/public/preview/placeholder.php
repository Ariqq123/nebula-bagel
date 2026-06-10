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

<html style="background-color: #050404;">
  <body>
    <div class="header">
      <div class="hint">
        <div class="hint-box">
          <?php
            // determine hint

            $hint = random_int(1,5);

            $hint_icon = "";
            $hint_text = "";

            if($hint == 1) {
              $hint_icon = "bi-stars";
              $hint_text = "<b>Enjoying Nebula so far?</b> We'd love to hear your experience through <b>submitting a review</b> on <a href='https://builtbybit.com/resources/nebula.32442/reviews?ref=449337' target='_blank'>BuiltByBit</a> or <a href='https://www.sourcexchange.net/products/nebula#reviews' target='_blank'>sourceXchange</a>.";
            } else if($hint == 2) {
              $hint_icon = "bi-puzzle-fill";
              $hint_text = "Nebula works with most of your <b>favorite extensions</b>. Expand your panel's functionality by stocking up on some new extensions <a href='https://blueprint.zip/browse' target='_blank'>here</a>.";
            } else if($hint == 3) {
              $hint_icon = "bi-chat-left-quote-fill";
              $hint_text = "<b>Suggest new features</b>, report bugs and provide feedback to help Nebula improve even further on our <a href='https://github.com/prplwtf/nebula/issues/new' target='_blank'>GitHub repository</a>.";
            } else if($hint == 4) {
              $hint_icon = "bi-palette2";
              $hint_text = "<b>Customize your color palette</b> and set your panel apart from others. Preview your colors live in Nebula's <a href='/extensions/nebula/editor/edit/palette.php' target='_blank'>palette editor</a>.";
            } else if($hint == 5) {
              $hint_icon = "bi-plus-square-fill";
              $hint_text = "<b>Effortlessly customize your settings</b> by clicking the <b>plus button</b> next to the configuration options for a complete personalization experience.";
            }

          ?>
          <div class="hint-icon">
            <i class="bi <?php echo $hint_icon; ?>"></i>
          </div>
          <div class="hint-text">
            <span>
              <?php echo $hint_text; ?>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="content-container">
      <div class="content">
        <p style="color: white; text-align: center; font-size: 55px;">
          <i class="bi bi-exclude"></i> <span style="font-weight: 600">Designer</span><br style="padding-bottom:4px"/>
          <code style="color: lightgray; text-align: center; font-size: 18px;">v2.2-2</code>
        </p>
      </div>
      <div class="footer">
        <p style="text-align:center"><a href="https://github.com/prplwtf/nebula/issues/new" target="_blank">
          <button class="bug-btn"><i class="bi bi-chat-left-fill" style="margin-right:3px"></i> Feedback</button>
        </a></p>
      </div>
    </div>
  </body>
</html>
<style>
  @import url("https://cdn.nebula.style/icons/bootstrap/bootstrap-icons.css");
  @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
  body { font-family: 'Inter', sans-serif; padding: 0px !important; margin: 0px !important; overflow: hidden; }

  html {
    height: 100%;
    width: 100%;
  }

  .content {
    position: absolute;
    left: 50%;
    top: 50%;
    -webkit-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
    width: 80%;
    padding-top: 70px;
  }
  .content-container {
    height: 100%;
    width: 100%;
    background-color: #141414;
    border-radius: 12px 12px 0 0;
  }

  .footer {
    position: fixed;
    width: 100%;
    bottom: 15px;
    left: 0;
  }

  .bug-btn {
    background-color:#222222;
    transition: background-color .3s;
    border-radius: 12px;
    border: none;
    padding: 10px 14px;
    color: #c2c2c2;
    font-size: 18px;
  }
  .bug-btn:hover {
    background-color: #363636;
  }

  @media screen and (max-height: 500px) {
    .hint, .header, .bug-btn {
      display: none;
    }
    body {
      background-color: #141414 !important;
    }
    .content {
      padding-top: 0 !important;
    }
  }

  .header {
    width: 100%;
    background-color: #050404;
    padding-bottom: 20px;
  }

  .hint {
    border-radius: 0 0 12px 12px;
    padding: 20px 25px;
    color: #60adff;
    background-color: #122853;
    padding-bottom: 22px;
  }
  .hint-box {
    display: flex;
    flex-direction: row;
    align-items: center;
  }
  .hint-icon {
    font-size: 25px;
    padding-right: 15px;
  }
  .hint-text {
    font-size: 15px;
  }

  .hint-text span > b {
    font-weight: 700;
  }
  .hint-text span > code {
    background-color: #1a3770;
    border-radius: 7px;
    padding: 7px 14px;
    overflow: hidden;
    width: auto;
    display: inline-flex;
  }
  .hint-text span > a {
    color: #60adff !important;
  }

</style>
