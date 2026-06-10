@if($n_alert == "1" && Auth::check())
  @if($n_website_links != '1') <div class="alert-spacer"></div> @endif
  <!-- Alert -->
  <div class="nebula-alert" style="--position: {{ $n_alert_position }}">
    <div class="alert-container">
      <div class="alert-icon">
        <i class="bi bi-{{ $n_alert_icon }}"></i>
      </div>
      <div class="alert-text"></div>
      <div class="alert-text-value" style="display:none">{{ nl2br(e($n_alert_text)) }} </div>
      @if($n_alert_dismiss)
        <div class="alert-close" onclick="DismissNebulaAlert()">
          <i class="bi bi-x"></i>
        </div>
      @endif
    </div>
  </div>
  <script>
    document.querySelector('div.alert-text').innerHTML = marked.parse(document.querySelector('div.alert-text-value').innerText+" ");
  </script>
@endif

@if($n_alert_dismiss)
  <script>
    function DismissNebulaAlert() {
      @if($n_website_links != '1') let alertSpacer = document.querySelector(".alert-spacer");
      @else let alertSpacer = document.querySelector(".nebula-weblinks"); @endif
      let alert = document.querySelector(".nebula-alert")
      alert.style.opacity = 0;
      alert.style.height = alert.offsetHeight+'px';
      setInterval(() => {
        alert.style.height = 0;
        alert.style.marginTop = 0;
        @if($n_website_links != '1')
          alertSpacer.style.marginBottom = 0
        @else
          alertSpacer.style.marginBottom = 10+'px'
        @endif
      }, 100);
      setInterval(() => {
        alert.style.display = "none";
      }, 750)
    }
  </script>
@endif