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
<style>
  .match-dashboard-bg {background-color: <?php echo $blueprint->dbGet("nebula", "palette_dashboard_7"); ?> !important;}
  .match-auth-bg {background-color: <?php echo $blueprint->dbGet("nebula", "palette_auth_1"); ?> !important;}
</style>

<body id="body">
  <iframe
    title="Dashboard Preview"
    class="dash-preview match-dashboard-bg"
    id="dashPreview"
    src="/">
  </iframe>
  <iframe
    title="Authentication Preview"
    class="auth-preview match-auth-bg"
    id="authPreview"
    src="/extensions/nebula/preview/auth.php">
  </iframe>
  <a class="focus" id="focus-dash" href="#body"></a>
  <a class="focus" id="focus-auth" href="#authPreview"></a>
</body>
<style>
  html {
    overflow-x: hidden;
    scroll-behavior: smooth;
  }
  body {
    overflow-x: hidden;
    overflow-y: scroll;
    margin: 0;
    margin-bottom: 20px;
  }
  .focus { display: none; }
  .auth-preview, .dash-preview {
    margin: 0;
    padding: 0;
    border: none;
    margin-top: 20px;
    margin-left: 20px;
    margin-right: 20px;
    height: calc(75% - 40px);
    width: calc(100% - 40px);
    overflow: hidden !important;
    border-radius: 12px;
    border: #ffffff20 2px solid;
  }

  *::-webkit-scrollbar {
    display: none;
  }
  * {
    -ms-overflow-style: none;
    scrollbar-width: none;
  } 
</style>
<script>
  window.onload = function() {
    var iframe = document.getElementById("dashPreview");
    var doc = iframe.contentDocument;
    doc.querySelector("html").style.overflow = "hidden";
  }
</script>