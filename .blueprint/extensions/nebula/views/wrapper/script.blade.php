<script>
  console.log("nebula#~ wrapper.blade.php")

  setTimeout(() => {
    console.debug("%c"+
    "\nIdentifier:   %cnebula"+
    "\n%cVersion:      %c2.2-2"+
    "\n%cBlueprint:    %cbeta-2026-05"+
    "\n%cAuthor:       %cprplwtf"+
    "\n%cPath:         %c/home/bagelhost/panel-nebula"+
    "\n%cData:         %c/home/bagelhost/panel-nebula/.blueprint/extensions/nebula/private"+
    "\n%cPublic:       %c/home/bagelhost/panel-nebula/.blueprint/extensions/nebula/public"+
    "\n%cType:         %clocal"+
    "\n%cTimestamp:    %c1780991480"+
      "\n ",

      'color: #3bb4f9; font-weight: 900',
      'color: #168cf4; font-weight: 500',
      'color: #3bb4f9; font-weight: 900',
      'color: #168cf4; font-weight: 500',
      'color: #3bb4f9; font-weight: 900',
      'color: #168cf4; font-weight: 500',
      'color: #3bb4f9; font-weight: 900',
      'color: #168cf4; font-weight: 500',
      'color: #3bb4f9; font-weight: 900',
      'color: #168cf4; font-weight: 500',
      'color: #3bb4f9; font-weight: 900',
      'color: #168cf4; font-weight: 500',
      'color: #3bb4f9; font-weight: 900',
      'color: #168cf4; font-weight: 500',
      'color: #3bb4f9; font-weight: 900',
      'color: #168cf4; font-weight: 500',
      'color: #3bb4f9; font-weight: 900',
      'color: #168cf4; font-weight: 500',
    );
  }, 5000);

  document.addEventListener("DOMContentLoaded", function () {
    console.log(
      "\n%cPowered by Nebula - © 2023-2026 Emma (prpl.wtf)\n%c\nLearn more at https://nebula.style \nBuyer agreements available at https://cdn.nebula.style/etc/policies/\n\n",

      'font-weight: 900; font-size: 24px; color: #7a98ff;',
      'font-weight: 900; color: #ffffff;'
    )
    @if(Auth::check())
      <?php if($n_website_links == "1") { echo 'insertAboveApp(".nebula-weblinks");'; } ?>
      <?php if($n_alert == "1" && $n_website_links != "1") { echo 'insertAboveApp(".alert-spacer");'; } ?>
      <?php if($n_alert == "1") { echo 'insertAboveApp(".nebula-alert");'; } ?>
    @endif
  });
</script>
<script>
  window.MultitaskingEnabled = false
  const params = new URL(window.location).searchParams;
  if(params.get('multitasking') == "true") {
    window.MultitaskingEnabled = true
    document.querySelector("html").setAttribute("multitasking", "")
    fetchStyle("/extensions/nebula/libraries/floatingWindowStyles.css", "Multitasking")
  }

  @if(Auth::user()->root_admin)
    const rootAdmin = true
  @else
    const rootAdmin = false
  @endif

  window.onload = function() {
    setInterval(() => { sidebarRefresh() }, 1000)
    filesRefresh()
  }

  window.addEventListener('locationchange', function () {
    setTimeout(() => {sidebarRefresh()}, 200)
  });

  function sidebarButtonEvent(btn) {
    serverId = fetchServerId()
    if(btn === "home") { document.querySelector("a:not([blueprint])[href='/']").click() };
    @if(Auth::user()->root_admin == 1) if(btn === "logout") { document.querySelector(".NavigationBar__RightNavigation-sc-tupl2x-0 > button:nth-child(5)").click() };
    @else if(btn === "logout") { document.querySelector(".NavigationBar__RightNavigation-sc-tupl2x-0 > button:nth-child(4)").click() }; @endif
    // servers
    if(btn === "serverTerminal") { document.querySelector("a:not([blueprint])[href='/server/"+serverId+"']").click() };
    if(btn === "serverFiles") { document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/files']").click() };
    if(btn === "serverDatabases") { document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/databases']").click() };
    if(btn === "serverSchedules") { document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/schedules']").click() };
    if(btn === "serverUsers") { document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/users']").click() };
    if(btn === "serverBackups") { document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/backups']").click() };
    if(btn === "serverNetwork") { document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/network']").click() };
    if(btn === "serverStartup") { document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/startup']").click() };
    if(btn === "serverSettings") { document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/settings']").click() };
    if(btn === "serverActivity") { document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/activity']").click() };
    // users
    if(btn === "accountAccount") { document.querySelector("a:not([blueprint])[href='/account']").click() };
    if(btn === "accountApi") { document.querySelector("a:not([blueprint])[href='/account/api']").click() };
    if(btn === "accountSsh") { document.querySelector("a:not([blueprint])[href='/account/ssh']").click() };
    if(btn === "accountActivity") { document.querySelector("a:not([blueprint])[href='/account/activity']").click() };

    if(mobileNavigationVisible == true) { closeMobileNav() }

    sidebarRefresh()
  }
  async function sidebarRefresh() {
    // Get current window path and assign it to a variable.
    currentPage = nebulaCurrentPage()
    fileModeHide()
    assignElements(currentPage)
    // Show selected button.
    if(currentPage === "home") {document.getElementById('sidebarMainHome').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarMainHome').className = 'sidebarButton';}
    if(currentPage.startsWith("account")) {
      document.getElementById('sidebarCategoryAccount').style.display = "inline";
      if(currentPage === "accountAccount") {document.getElementById('sidebarAccountAccount').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarAccountAccount').className = 'sidebarButton';}
      if(currentPage === "accountApi") {document.getElementById('sidebarAccountApi').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarAccountApi').className = 'sidebarButton';}
      if(currentPage === "accountSsh") {document.getElementById('sidebarAccountSsh').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarAccountSsh').className = 'sidebarButton';}
      if(currentPage === "accountActivity") {document.getElementById('sidebarAccountActivity').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarAccountActivity').className = 'sidebarButton';}
      <?php if($n_page_indexing == "1") { echo "if(currentPage === \"accountMore\") {document.getElementById('sidebarAccountMore').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarAccountMore').className = 'sidebarButton';}"; }; ?>

      setTimeout(() => {
        sidebarMoreRefresh()
      }, 300);
    } else {document.getElementById('sidebarCategoryAccount').style.display = "none";}
    if(currentPage.startsWith("server")) {
      document.getElementById('sidebarCategoryServer').style.display = "inline";
      if(currentPage === "serverTerminal") {document.getElementById('sidebarServerTerminal').className = 'sidebarButton sidebarButtonSelected';refreshStatusOrb() } else {document.getElementById('sidebarServerTerminal').className = 'sidebarButton';}
      if(currentPage === "serverFiles") {document.getElementById('sidebarServerFilemanager').className = 'sidebarButton sidebarButtonSelected';fileModeShow() }
      if(currentPage === "serverFilesEdit") {document.getElementById('sidebarServerFilemanager').className = 'sidebarButton sidebarButtonSelected'; }
      if((currentPage != "serverFiles") && (currentPage != "serverFilesEdit")) { document.getElementById('sidebarServerFilemanager').className = 'sidebarButton'; }
      if(currentPage === "serverDatabases") {document.getElementById('sidebarServerDatabases').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarServerDatabases').className = 'sidebarButton';}
      if(currentPage === "serverSchedules") {document.getElementById('sidebarServerSchedules').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarServerSchedules').className = 'sidebarButton';}
      if(currentPage === "serverUsers") {document.getElementById('sidebarServerUsers').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarServerUsers').className = 'sidebarButton';}
      if(currentPage === "serverBackups") {document.getElementById('sidebarServerBackups').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarServerBackups').className = 'sidebarButton';}
      if(currentPage === "serverNetwork") {document.getElementById('sidebarServerNetwork').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarServerNetwork').className = 'sidebarButton';}
      if(currentPage === "serverStartup") {document.getElementById('sidebarServerStartup').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarServerStartup').className = 'sidebarButton';}
      if(currentPage === "serverSettings") {document.getElementById('sidebarServerSettings').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarServerSettings').className = 'sidebarButton';}
      if(currentPage === "serverActivity") {document.getElementById('sidebarServerActivity').className = 'sidebarButton sidebarButtonSelected';} else {document.getElementById('sidebarServerActivity').className = 'sidebarButton';}
      <?php if($n_page_indexing == "1") { echo 'if(currentPage === "serverMore") {document.getElementById(\'sidebarServerMore\').className = \'sidebarButton sidebarButtonSelected\';} else {document.getElementById(\'sidebarServerMore\').className = \'sidebarButton\';}'; } ?>

      setTimeout(() => {
        serverId = fetchServerId()
        sidebarServerButtonRefresh(serverId);
        <?php if($n_page_indexing == "1") { echo "sidebarMoreRefresh()"; } ?>
      }, 300)
    } else {document.getElementById('sidebarCategoryServer').style.display = "none";}

    @if($n_sidebar_always_visible_buttons == "0")
    if(currentPage == "home") { document.getElementById('sidebarCategoryGeneral').style.display = "inline" }
    else { document.getElementById('sidebarCategoryGeneral').style.display = "none" }
    @endif
  }
  function sidebarServerButtonRefresh(serverId) {
    nopage=true
    if(document.querySelector("a:not([blueprint])[href='/server/"+serverId+"']") === null) {
      document.getElementById('sidebarServerTerminal').style.display = "none";
    } else {
      document.getElementById('sidebarServerTerminal').style.display = "inline";
      nopage=false
    }
    if(document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/files']") === null) {
      document.getElementById('sidebarServerFilemanager').style.display = "none";
    } else {
      document.getElementById('sidebarServerFilemanager').style.display = "inline";
      nopage=false
    }
    if(document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/databases']") === null) {
      document.getElementById('sidebarServerDatabases').style.display = "none";
    } else {
      document.getElementById('sidebarServerDatabases').style.display = "inline";
      nopage=false
    }
    if(document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/schedules']") === null) {
      document.getElementById('sidebarServerSchedules').style.display = "none";
    } else {
      document.getElementById('sidebarServerSchedules').style.display = "inline";
      nopage=false
    }
    if(document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/users']") === null) {
      document.getElementById('sidebarServerUsers').style.display = "none";
    } else {
      document.getElementById('sidebarServerUsers').style.display = "inline";
      nopage=false
    }
    if(document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/backups']") === null) {
      document.getElementById('sidebarServerBackups').style.display = "none";
    } else {
      document.getElementById('sidebarServerBackups').style.display = "inline";
      nopage=false
    }
    if(document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/network']") === null) {
      document.getElementById('sidebarServerNetwork').style.display = "none";
    } else {
      document.getElementById('sidebarServerNetwork').style.display = "inline";
      nopage=false
    }
    if(document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/startup']") === null) {
      document.getElementById('sidebarServerStartup').style.display = "none";
    } else {
      document.getElementById('sidebarServerStartup').style.display = "inline";
      nopage=false
    }
    if(document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/settings']") === null) {
      document.getElementById('sidebarServerSettings').style.display = "none";
    } else {
      document.getElementById('sidebarServerSettings').style.display = "inline";
      nopage=false
    }
    if(document.querySelector("a:not([blueprint])[href='/server/"+serverId+"/activity']") === null) {
      document.getElementById('sidebarServerActivity').style.display = "none";
    } else {
      document.getElementById('sidebarServerActivity').style.display = "inline";
      nopage=false
    }
    // if nopage = true then no page buttons are appearing.
    if(nopage == true) { document.getElementById('sidebarServerLoader').style.display = 'block'; }
    else { document.getElementById('sidebarServerLoader').style.display = 'none'; }
    }
  function sidebarMoreRefresh() {
    // show/hide 'more' button
    showMore=false
    document.querySelectorAll('#SubNavigation div a').forEach((el) => {
      if([
        '',
        'Account',
        'API Credentials',
        'SSH Keys',
        'Console',
        'Files',
        'Databases',
        'Schedules',
        'Users',
        'Backups',
        'Network',
        'Startup',
        'Settings',
        'Activity',
      ].includes(el.textContent)) { return; }
      showMore=true;
    });
    if(showMore == true) {
      document.getElementById('sidebarAccountMore').style.display = "inline";
      document.getElementById('sidebarServerMore').style.display = "inline";
    } else {
      document.getElementById('sidebarAccountMore').style.display = "none";
      document.getElementById('sidebarServerMore').style.display = "none";
    }
  }
</script>
