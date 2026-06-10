<style id="nebula-variables">
  <?php
    $__transparency="";
    if($n_dashboard_transparency == "1") { $__transparency="BB"; }
    elseif($n_dashboard_transparency == "2") { $__transparency="99"; }
    elseif($n_dashboard_transparency == "3") { $__transparency="60"; }
  ?>

  /* Variables */
  :root {
    --sidebarPrimary: {{ $n_palette_sidebar_1 }};
    --sidebarPrimaryHover: {{ $n_palette_sidebar_2 }};
    --sidebarSecondary: {{ $n_palette_sidebar_3 }};
    --sidebarSecondaryHover: {{ $n_palette_sidebar_4 }};
    --sidebarSecondaryActive: {{ $n_palette_sidebar_5 }};
    --sidebarSecondarySelected: {{ $n_palette_sidebar_6 }};
    --sidebarButtonActive: {{ $n_palette_sidebar_8 }};

    --pagePrimary: {{ $n_palette_dashboard_1 }};
    --pagePrimaryHover: {{ $n_palette_dashboard_2 }};
    --pageSecondary: {{ $n_palette_dashboard_3 }}{{ $__transparency }};
    --pageSecondaryHover: {{ $n_palette_dashboard_4 }}{{ $__transparency }};
    --pageSecondaryActive: {{ $n_palette_dashboard_5 }}{{ $__transparency }};
    --pageSecondarySelected: {{ $n_palette_dashboard_6 }}{{ $__transparency }};
    --pageButtonDefault: {{ $n_palette_dashboard_8 }};
    --pageButtonHover: {{ $n_palette_dashboard_9 }};

    --statusOffline: {{ $n_palette_status_offline }};
    --statusError: {{ $n_palette_status_error }};
    --statusStarting: {{ $n_palette_status_starting }};
    --statusOnline: {{ $n_palette_status_online }};

    --authA: {{ $n_palette_auth_1 }};
    --authB: {{ $n_palette_auth_2 }};
    --authC: {{ $n_palette_auth_3 }};
    --authD: {{ $n_palette_auth_4 }};
    --authE: {{ $n_palette_auth_5 }};
    --authF: {{ $n_palette_auth_6 }};
    --authG: {{ $n_palette_auth_7 }};
    --authH: {{ $n_palette_auth_8 }};
    
    --sidebarBackground: {{ $n_palette_sidebar_7 }};
    --pageBackground: {{ $n_palette_dashboard_7 }};

    --borderRadius: {{ $n_border_radius }}px;
    --borderRadiusSidebar: {{ $n_sidebar_border_radius }}px;
    --borderRadiusAuth: 10px;

    --patternSizeAuth: {{ $n_auth_background_magicsize }}px;
    --patternSizeDashboard: {{ $n_background_magicsize }}px;
  }

</style>