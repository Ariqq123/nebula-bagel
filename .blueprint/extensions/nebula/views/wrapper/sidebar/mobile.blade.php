<style id="nebula-mobile-navigation">
  .nebula-mobile-nav { display: none }

  @media screen and (max-width: 760px) {
    body, body.bg-neutral-800 {
      padding-left: unset !important;
    }

    .sidebar {
      display: none;
      z-index: 101;
    }
    div.ProgressBar___StyledDiv-sc-14ayc3f-1.jleFWY {
      left: 0 !important;
      width: 100% !important;
    }
    .nebula-mobile-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 80px;
      z-index: 99;
      /*background-color: var(--sidebarBackground);
      border-top: 1px solid var(--sidebarSecondaryHover);*/
      display: flex;
      justify-content: center;
      flex-direction: row;
      background: transparent;
      background: linear-gradient(180deg, transparent 0%, var(--pageBackground) 100%); 
    }
    .mobile-nav-container {
      margin-left: 15px;
      margin-right: 15px;
    }

    .mobile-nav-icon {
      font-size: 20px;
      color: var(--sidebarPrimary);
    }
    .mobile-nav-button {
      background-color: var(--sidebarSecondary);
      border-radius: 100px;
      padding-left: 25px;
      padding-right: 25px;
      padding-top: 10px;
      padding-bottom: 10px;
    }

    .nebula-mobile-overlay {
      width: 100%;
      height: 100%;
      opacity: 0;
      display: none;
      position: fixed;
      left: -200px;
      top: 0;
      transition: opacity .4s, left .4s;
      z-index: 100;
      background: var(--pageBackground);
      background: linear-gradient(90deg, var(--pageBackground) 75%, transparent 100%);
    }

    .pointer-events-none.fixed.bottom-0.mb-6.flex.justify-center.w-full.z-50:has(.ipOPTy) {
      margin-bottom: 0px !important;
      margin-top: 20px !important;
      bottom: unset !important;
      top: 0px !important;
    }
    #fileMode {
      bottom: unset !important;
      top: 20px !important;
    }

     /* loading bar styling */
    .CbttB {
      top: 0;
      bottom: unset !important;
    }

    .Pagination___StyledDiv-sc-7j9cqz-1 {
      bottom: 105px !important;
      width: 100vw !important;
      left: 0px !important;
      right: unset !important;
    }
  }
</style>

@if(Auth::check())
  <div class="nebula-mobile-overlay" onclick="closeMobileNav()"></div>
  <div class="nebula-mobile-nav">
    <div class="mobile-nav-container">
      <!-- Home -->
      <button class="mobile-nav-button" onclick="sidebarRefresh();sidebarButtonEvent('home')"><i class="mobile-nav-icon bi bi-house-fill"></i></button>
    </div>
    <div class="mobile-nav-container">
      <!-- Account -->
      <button class="mobile-nav-button" onclick="sidebarRefresh();sidebarButtonEvent('accountAccount')"><i class="mobile-nav-icon bi bi-person-fill"></i></button>
    </div>
    <div class="mobile-nav-container">
      <!-- Navigation -->
      <button class="mobile-nav-button" onclick="sidebarRefresh();openMobileNav()"><i class="mobile-nav-icon bi bi-list"></i></button>
    </div>
  </div>
  <script>
    mobileNavigationVisible=false
    navPossible=true
    function toggleMobileNav() {
      if(mobileNavigationVisible) {
        closeMobileNav()
      } else {
        openMobileNav()
      }
    }
    function closeMobileNav() {
      if(navPossible == false) {return;}
      navPossible = false
      let sidebar = document.querySelector(".sidebar")
      let overlay = document.querySelector(".nebula-mobile-overlay")
      sidebar.style.left = "-200px"
      overlay.style.left = null
      overlay.style.opacity = null
      setTimeout(() => {
        sidebar.style.display = null
        overlay.style.display = null
        navPossible = true
      },300)
      mobileNavigationVisible=false
    }
    function openMobileNav() {
      if(navPossible == false) {return;}
      navPossible = false
      let sidebar = document.querySelector(".sidebar")
      let overlay = document.querySelector(".nebula-mobile-overlay")
      sidebar.style.left = "-200px"
      overlay.style.left = "-200px"
      overlay.style.opacity = "0"
      sidebar.style.display = "block"
      overlay.style.display = "block"
      setTimeout(() => {
        overlay.style.opacity = ".7"
        sidebar.style.left = "0px"
        overlay.style.left = "0px"
        navPossible = true
      },50)
      mobileNavigationVisible=true
    }
  </script>
@endif