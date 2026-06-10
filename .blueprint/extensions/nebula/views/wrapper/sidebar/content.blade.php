<div class="sidebarContentContainer">
  <div class="sidebarContent">

    @if($n_sidebar_full == "1" && $n_sidebar_customlogo != "")
      <img class="customlogo" src="{{ $n_sidebar_customlogo }}" onclick="sidebarRefresh();sidebarButtonEvent('home')"></img>
    
      <!-- Item: Spacer -->
      <div class="sidebarSpacer"></div>
    @endif

    <!-- Item: Home -->
    <div class="tooltip-toggle">
      <span class="tooltip">Home</span>
      <button data-tippy-content="Home" oncontextmenu="showContextMenu(event, `/`)" onclick="sidebarRefresh();sidebarButtonEvent('home')" id="sidebarMainHome" class="sidebarButton">
        @if($n_sidebar_home == "" || $n_sidebar_full == "1")
        <i class="sidebarIcon {{ $__home }}"></i>
        @else
        <img class="customicon" src="{{ $n_sidebar_home }}"></img>
        @endif
        @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Home</span>@endif
      </button>
    </div>

    <!-- Item: Spacer -->
    <div class="sidebarSpacer"></div>
    
    <!-- Category: Server Management -->
    <div id="sidebarCategoryServer" class="sidebarCategory">

      <div id="sidebarServerLoader" style="display: none">
        <button class="sidebar-placeholder-animated sidebarButton"> </button>
        <button class="sidebar-placeholder-animated sidebarButton"> </button>
        <button class="sidebar-placeholder-animated sidebarButton"> </button>
        <button class="sidebar-placeholder-animated sidebarButton"> </button>
        <button class="sidebar-placeholder-animated sidebarButton"> </button>
        <button class="sidebar-placeholder-animated sidebarButton"> </button>
        <button class="sidebar-placeholder-animated sidebarButton"> </button>
        <button class="sidebar-placeholder-animated sidebarButton"> </button>
        <button class="sidebar-placeholder-animated sidebarButton"> </button>
        <button class="sidebar-placeholder-animated sidebarButton"> </button>
      </div>

      <!-- Item: Terminal -->
      <div class="tooltip-toggle">
        <span class="tooltip">Terminal</span>
        <button data-tippy-content="Terminal" oncontextmenu="showContextMenu(event, `/server/${fetchServerId()}`)" onclick="sidebarRefresh();sidebarButtonEvent('serverTerminal')" id="sidebarServerTerminal" class="sidebarButton">
          @if($n_sidebar_server_terminal == "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_terminal }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_terminal }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Terminal</span>@endif
        </button>
      </div>
      
      <!-- Item: File Manager -->
      <div class="tooltip-toggle">
        <span class="tooltip">Files</span>
        <button data-tippy-content="Files" oncontextmenu="showContextMenu(event, `/server/${fetchServerId()}/files`)" onclick="sidebarRefresh();sidebarButtonEvent('serverFiles')" id="sidebarServerFilemanager" class="sidebarButton">
          @if($n_sidebar_server_files == "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_files }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_files }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Files</span>@endif
        </button>
      </div>
      
      <!-- Item: Databases -->
      <div class="tooltip-toggle">
        <span class="tooltip">Databases</span>
        <button data-tippy-content="Databases" oncontextmenu="showContextMenu(event, `/server/${fetchServerId()}/databases`)" onclick="sidebarRefresh();sidebarButtonEvent('serverDatabases')" id="sidebarServerDatabases" class="sidebarButton">
          @if($n_sidebar_server_databases == "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_databases }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_databases }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Databases</span>@endif
        </button>
      </div>
      
      <!-- Item: Schedules -->
      <div class="tooltip-toggle">
        <span class="tooltip">Schedules</span>
        <button data-tippy-content="Schedules" oncontextmenu="showContextMenu(event, `/server/${fetchServerId()}/schedules`)" onclick="sidebarRefresh();sidebarButtonEvent('serverSchedules')" id="sidebarServerSchedules" class="sidebarButton">
          @if($n_sidebar_server_schedules== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_schedules }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_schedules }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Schedules</span>@endif
        </button>
      </div>
      
      <!-- Item: Users -->
      <div class="tooltip-toggle">
        <span class="tooltip">Users</span>
        <button data-tippy-content="Users" oncontextmenu="showContextMenu(event, `/server/${fetchServerId()}/users`)" onclick="sidebarRefresh();sidebarButtonEvent('serverUsers')" id="sidebarServerUsers" class="sidebarButton">
          @if($n_sidebar_server_users== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_users }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_users }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Users</span>@endif
        </button>
      </div>
      
      <!-- Item: Backups -->
      <div class="tooltip-toggle">
        <span class="tooltip">Backups</span>
        <button data-tippy-content="Backups" oncontextmenu="showContextMenu(event, `/server/${fetchServerId()}/backups`)" onclick="sidebarRefresh();sidebarButtonEvent('serverBackups')" id="sidebarServerBackups" class="sidebarButton">
          @if($n_sidebar_server_backups== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_backups }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_backups }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Backups</span>@endif
        </button>
      </div>
      
      <!-- Item: Network -->
      <div class="tooltip-toggle">
        <span class="tooltip">Network</span>
        <button data-tippy-content="Network" oncontextmenu="showContextMenu(event, `/server/${fetchServerId()}/network`)" onclick="sidebarRefresh();sidebarButtonEvent('serverNetwork')" id="sidebarServerNetwork" class="sidebarButton">
          @if($n_sidebar_server_network== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_network }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_network }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Network</span>@endif
        </button>
      </div>
      
      <!-- Item: Startup -->
      <div class="tooltip-toggle">
        <span class="tooltip">Startup</span>
        <button data-tippy-content="Startup" oncontextmenu="showContextMenu(event, `/server/${fetchServerId()}/startup`)" onclick="sidebarRefresh();sidebarButtonEvent('serverStartup')" id="sidebarServerStartup" class="sidebarButton">
          @if($n_sidebar_server_startup== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_startup }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_startup }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Startup</span>@endif
        </button>
      </div>
      
      <!-- Item: Settings -->
      <div class="tooltip-toggle">
        <span class="tooltip">Settings</span>
        <button data-tippy-content="Settings" oncontextmenu="showContextMenu(event, `/server/${fetchServerId()}/settings`)" onclick="sidebarRefresh();sidebarButtonEvent('serverSettings')" id="sidebarServerSettings" class="sidebarButton">
          @if($n_sidebar_server_settings== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_settings }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_settings }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Settings</span>@endif
        </button>
      </div>
      
      <!-- Item: Activity -->
      <div class="tooltip-toggle">
        <span class="tooltip">Activity</span>
        <button data-tippy-content="Activity" oncontextmenu="showContextMenu(event, `/server/${fetchServerId()}/activity`)" onclick="sidebarRefresh();sidebarButtonEvent('serverActivity')" id="sidebarServerActivity" class="sidebarButton">
          @if($n_sidebar_server_activity== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_activity }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_activity }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Activity</span>@endif
        </button>
      </div>

      @if($n_page_indexing == "1")
      <!-- Item: More -->
      <div class="tooltip-toggle">
        <span class="tooltip">More</span>
        <button data-tippy-content="More" style="display:none" id="sidebarServerMore" class="sidebarButton">
          @if($n_sidebar_server_more== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__server_more }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_server_more }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">More</span>@endif
        </button>
      </div>
      @endif
      
      @if($n_sidebar_always_visible_buttons == "1")
      <!-- Item: Spacer -->
      <div class="sidebarSpacer"></div>
      @endif
      
    </div>
    
    <!-- Category: Account -->
    <div id="sidebarCategoryAccount" class="sidebarCategory">
      
      <!-- Item: Account -->
      <div class="tooltip-toggle">
        <span class="tooltip">Account</span>
        <button data-tippy-content="Account" oncontextmenu="showContextMenu(event, `/account`)" onclick="sidebarRefresh();sidebarButtonEvent('accountAccount')" id="sidebarAccountAccount" class="sidebarButton">
          @if($n_sidebar_account_account== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__account_account }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_account_account }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Account</span>@endif
        </button>
      </div>
      
      <!-- Item: API Credentials -->
      <div class="tooltip-toggle">
        <span class="tooltip">API</span>
        <button data-tippy-content="API" oncontextmenu="showContextMenu(event, `/account/api`)" onclick="sidebarRefresh();sidebarButtonEvent('accountApi')" id="sidebarAccountApi" class="sidebarButton">
          @if($n_sidebar_account_api== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__account_api }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_account_api }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">API</span>@endif
        </button>
      </div>
      
      <!-- Item: SSH Keys -->
      <div class="tooltip-toggle">
        <span class="tooltip">SSH</span>
        <button data-tippy-content="SSH" oncontextmenu="showContextMenu(event, `/account/ssh`)" onclick="sidebarRefresh();sidebarButtonEvent('accountSsh')" id="sidebarAccountSsh" class="sidebarButton">
          @if($n_sidebar_account_ssh== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__account_ssh }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_account_ssh }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">SSH Keys</span>@endif
        </button>
      </div>
      
      <!-- Item: Activity -->
      <div class="tooltip-toggle">
        <span class="tooltip">Activity</span>
        <button data-tippy-content="Activity" oncontextmenu="showContextMenu(event, `/account/activity`)" onclick="sidebarRefresh();sidebarButtonEvent('accountActivity')" id="sidebarAccountActivity" class="sidebarButton">
          @if($n_sidebar_account_activity== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__account_activity }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_account_activity }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Activity</span>@endif
        </button>
      </div>

      @if($n_page_indexing == "1")
      <!-- Item: More -->
      <div class="tooltip-toggle">
        <span class="tooltip">More</span>
        <button data-tippy-content="More" style="display:none" id="sidebarAccountMore" class="sidebarButton">
          @if($n_sidebar_account_more== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__account_more }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_account_more }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">More</span>@endif
        </button>
      </div>
      @endif
      
      @if($n_sidebar_always_visible_buttons == "1")
      <!-- Item: Spacer -->
      <div class="sidebarSpacer"></div>
      @endif
      
    </div>

    <!-- Category: General -->
    <div id="sidebarCategoryGeneral">

      @if(Auth::user()->root_admin == 1)
      <!-- Item: Configuration -->
      <a href="/admin">
        <div class="tooltip-toggle">
          <span class="tooltip">Admin</span>
          <button data-tippy-content="Admin" oncontextmenu="showContextMenu(event, `/admin`)" onclick="sidebarRefresh();sidebarButtonEvent('admin')" id="sidebarMainConfiguration" class="sidebarButton">
            @if($n_sidebar_admin== "" || $n_sidebar_full == "1")
            <i class="sidebarIcon {{ $__admin }}"></i>
            @else
            <img class="customicon" src="{{ $n_sidebar_admin }}"></img>
            @endif
            @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Admin</span>@endif
          </button>
        </div>
      </a>
      @endif
      
      <!-- Item: Account -->
      <div class="tooltip-toggle">
        <span class="tooltip">Account</span>
        <button data-tippy-content="Account" oncontextmenu="showContextMenu(event, `/account`)" onclick="sidebarRefresh();sidebarButtonEvent('accountAccount')" id="sidebarMainAccount" class="sidebarButton">
          @if($n_sidebar_account== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__account }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_account }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Account</span>@endif
        </button>
      </div>
      
      <!-- Item: Log out -->
      <div class="tooltip-toggle">
        <span class="tooltip">Logout</span>
        <button data-tippy-content="Logout" oncontextmenu="return false;" onclick="sidebarRefresh();sidebarButtonEvent('logout')" id="sidebarMainLogout" class="sidebarButton">
          @if($n_sidebar_logout== "" || $n_sidebar_full == "1")
          <i class="sidebarIcon {{ $__logout }}"></i>
          @else
          <img class="customicon" src="{{ $n_sidebar_logout }}"></img>
          @endif
          @if($n_sidebar_full == "1")<span class="wideSidebarSpan" style="color: var(--sidebarPrimary)">Logout</span>@endif
        </button>
      </div>
    
    </div> 
    
  </div>
</div>

@if($n_sidebar_hover_tooltip == "2" && $n_sidebar_full == "0")
  <style>.tippy-box[data-animation=shift-away][data-state=hidden]{opacity:0}.tippy-box[data-animation=shift-away][data-state=hidden][data-placement^=top]{transform:translateY(10px)}.tippy-box[data-animation=shift-away][data-state=hidden][data-placement^=bottom]{transform:translateY(-10px)}.tippy-box[data-animation=shift-away][data-state=hidden][data-placement^=left]{transform:translateX(10px)}.tippy-box[data-animation=shift-away][data-state=hidden][data-placement^=right]{transform:translateX(-10px)}</style>
  <script>tippy('.sidebarButton', {placement: 'right', animation: 'shift-away', arrow: false});</script>
  <style>
    .tippy-box {
      background-color: var(--sidebarSecondaryActive);
      color: var(--sidebarPrimary);
      border-radius: var(--borderRadiusSidebar);
      @if($n_sidebar_hover == "popout")
        margin-left: 7px;
      @endif
    }
  </style>
@endif