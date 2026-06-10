@if(Auth::check())
<script>
  document.querySelector("head > meta[name='theme-color'][content='#0e4688']").setAttribute("content", "{{ $n_palette_dashboard_7 }}")
</script>
@endif
<!-- PTERODACTYL PANEL THEMING -->
<style id="nebula-theme">
  ::-webkit-scrollbar {
    width: 3px !important;
  }
  ::-webkit-scrollbar-thumb {
    box-shadow: unset !important;
    border-radius: 4px !important;
    background-color: color-mix(in hsl, white 15%, transparent) !important;
  }

  /* Pterodactyl */
  #NavigationBar,
  #SubNavigation {
    display: none;
    background-color: var(--pageBackground);
    opacity: 0;
    transition: none;
  }

  @if($n_background_image == "")
    [class*="App___StyledDiv-sc"] {
      background-color: var(--pageBackground)
    }
  @else
    [class*="App___StyledDiv-sc"] {
      background-color: #00000000
    }
  @endif

  [class*="ProgressBar___StyledDiv-sc"] {
    position: fixed;
    z-index: 6;
    top: 0;
    left: 70px;
    width: calc(100% - 70px);
  }
  .bg-neutral-900, .bg-gray-900, .bg-neutral-700 {
    background-color: var(--pageSecondaryActive)
  }

  .ZkNLd:not([type="checkbox"]):not([type="radio"])
  .jqTCDz:not([type="checkbox"]):not([type="radio"]),
  [class*="Input-sc"]:not([type="checkbox"]):not([type="radio"]) {
    background: none;
    background-color: var(--pageSecondaryActive);
    border: none;
  }
  input.form-input.styles-module_S9h-xMSg.styles-module_AWe-iPIe {
    background: none;
    background-color: var(--pageSecondaryActive);
    border: none;
  }
  select[class*="Select-sc"] {
    background-color: var(--pageSecondaryActive);
    border: none;
  }
  div.ContentBox___StyledDiv-sc-mjlt6f-2.iGOcRf {
    background-color: var(--pageSecondary)
  }
  .DropdownMenu___StyledDiv-sc-1ojgpy2-1,
  .FileDropdownMenu___StyledDiv-sc-17ln8oh-3 > .Fade__Container-sc-1p0gm8n-0 {
    display: none
  }
  .FileEditContainer___StyledDiv5-sc-48rzpu-9.arKOj {
    background-color: transparent !important;
  }
  [id*="floating-ui-"] {
    z-index: 9999 !important;
  }

  /* status bars on server cards */
  .fwbDSe .status-bar { --ActiveColor: var(--statusOffline) }
  .kVijQB .status-bar { --ActiveColor: var(--statusStarting) }
  .fRwFrz .status-bar { --ActiveColor: var(--statusOnline) }
  @if($n_statusgradient_style == "flat")
    .status-bar { background: transparent !important }
  @endif

  /*error! and alerts/messagebox*/
  .icGkbh {
    border: none !important;
    border-color: transparent !important;
    padding: 1rem !important;
  }
  .ErrorBoundary___StyledDiv2-sc-gjlwx5-1.fqqyUj {
    padding: 20px !important;
    border-radius: var(--borderRadius) !important;
    background-color: var(--pageBackground) !important;
    color: var(--pageButtonDefault) !important
  }
  [class*="MessageBox__Container-sc"] {
    background: var(--pageSecondaryHover);
    border: unset;
    border-radius: var(--borderRadius);
    padding: 15px;
  }
  .MessageBox___StyledSpan-sc-1yg9bob-2.kNkGJR.title {
    background: var(--pageSecondarySelected) !important;
  }

  /*texts*/
  .hojpzx, .jeOYKC, [class*="Button___StyledSpan-sc"] {
    font-weight: 600 !important;
    font-size: 1rem !important;
    text-transform: capitalize !important;
  }

  /* switches */
  .cDmYBr > label {
    background-color: color-mix(in hsl, var(--pageSecondaryActive) 90%, white);
    transition: background-color .3s;
  }
  .cDmYBr > input[type="checkbox"]:checked + label {
    background-color: var(--pagePrimaryHover);
  }

  /*screenblock*/
  [class*="ScreenBlock___StyledDiv2-sc"] { background-color: transparent; width: 100%; box-shadow: none; }
  [class*="ScreenBlock___StyledH-sc"],
  [class*="ScreenBlock___StyledP-sc"] { color: white; }
  [class*="ScreenBlock___StyledImg-sc"] { max-width: 420px; }
  [class*="Button__ButtonStyle-sc"][class*="ScreenBlock__ActionButton-sc"] {
    animation: unset !important;
    width: auto;
    padding-left: 13px;
    padding-right: 13px;
  }
  [class*="Button__ButtonStyle-sc"][class*="ScreenBlock__ActionButton-sc"] > [class*="Button___StyledSpan-sc"]::after {
    content: 'Refresh';
    margin-left: 6px;
  }
  .erSjDQ {
    position: absolute;
    left: 0px;
    top: 15px;
    margin-left: unset !important;
    margin-top: unset !important;
    display: ruby;
    width: 100%;
  }
  [class*="ScreenBlock___StyledImg-sc"] {
    margin-top: 72px !important
  }
  [class*="ScreenBlock___StyledDiv2-sc"] {
    margin: unset !important;
    padding: unset !important;
  }

  [class*="WebsocketHandler___StyledDiv-sc"] {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 41;
    border-radius: var(--borderRadius);
    padding-left: 15px;
    padding-right: 15px;
    padding-top: 10px;
    padding-bottom: 10px;
  }

  .style-module_3kBDV_wo, .cDkCmT, .gfhYPP {
    --thisButtonColor: var(--pageButtonDefault); /*#474849*/
    --tw-ring-opacity: 0 !important;
    --tw-ring-color: #00000000 !important;
    --tw-ring-offset-width: 0px !important;
    font-weight: 600 !important;
    background: none !important;
    @if($n_dashboard_transparency == "0")
    background-color: var(--thisButtonColor) !important;
    border: none !important;
    @else
    background-color: var(--pageSecondary) !important;
    @endif
    transition: background-color .4s !important;
   }
  @if($n_dashboard_transparency == "0")
  .style-module_3kBDV_wo:hover, .cDkCmT:hover, .gfhYPP:hover { --thisButtonColor: var(--pageButtonHover) } /*#525354*/
  .style-module_Yp7-2Fw- { --thisButtonColor: var(--pageSecondaryHover) }
  .style-module_Yp7-2Fw-:hover { background-color: var(--pageSecondaryActive) !important; }
  @else
  .style-module_3kBDV_wo:hover, .cDkCmT:hover, .gfhYPP:hover { background-color: var(--pageSecondary) !important }
  .style-module_Yp7-2Fw- { background-color: var(--pageSecondary) !important }
  .style-module_Yp7-2Fw-:hover { background-color: var(--pageSecondary) !important }
  @endif

  /* Modal 1 */
  div.Modal___StyledDiv2-sc-9vzni8-3 { background-color: var(--pageSecondary) }
  .bOwzFe { z-index: 101 !important }
  /* Modal 2 */
  div.style-module_1RnhIT0w div.flex.p-6.pb-0.overflow-y-auto { background-color: var(--pageSecondary) }
  div.style-module_1RnhIT0w { background-color: var(--pageSecondary) }
  div.px-6.py-3.bg-gray-700.flex.items-center.justify-end.space-x-3.rounded-b {
    background-color: var(--pageSecondary);
    padding-bottom: 1rem !important;
    padding-left: 1rem !important;
    padding-right: 1.25rem !important;
  }
  div.style-module_1RnhIT0w { border: var(--pageSecondary) 5px solid; --tw-ring-shadow: none; }
  div.fixed:nth-child(1) { background-color: #00000075 }
  button.style-module_4LBM1DKx.style-module_3kBDV_wo.style-module_3xOG8K0n.style-module_2UCZLAAp.style-module_Yp7-2Fw-.group {
    background-color: var(--pageSecondaryHover);
  }
  div#headlessui-portal-root div div div div.z-40 { z-index: 101 !important; }
  div#headlessui-portal-root div div div > div.z-50 { z-index: 102 !important; }
  .kztmyO { border-width: 0px !important; background-color: transparent !important; }
  /* search modal */
  a.fKxTnL[href*="/server/"] {
    background-color: var(--pageSecondary);
    transition: background-color .3s;
    box-shadow: unset !important;
    border: unset !important;
    border-color: transparent !important;
    border-width: 0px !important;
    border-radius: var(--borderRadius);
  }
  a.fKxTnL[href*="/server/"]:hover {
    background-color: var(--pageSecondaryHover);
  }
  .SearchModal___StyledSpan-sc-e8elnt-6.epzEyg {
    background-color: var(--pageSecondaryHover) !important;
  }

  /* loading bar styling */
  .CbttB {
    box-shadow: unset !important;
    background-color: var(--pagePrimaryHover);
    position: fixed;
    bottom: 0;
    left: 0;
    height: 2px !important;
    opacity: 1;
    transition:
      opacity .4s,
      width .5s !important;
    z-index: 6 !important;
  }
  .CbttB.fade-exit {
    opacity: 0 !important;
  }
  .fade-enter {
    opacity: 0 !important;
  }

  /* spinners */
  /* modal spinner */
  .Modal___StyledDiv-sc-9vzni8-2:has(.Spinner__SpinnerComponent-sc-p63ahb-0) {
    background: color-mix(in hsl, var(--pageSecondary) 80%, transparent) !important;
    border-radius: var(--borderRadius) !important;
  }
  /* console/codemirror spinner */
  .style-module_RcP2_Fvj.relative > .Fade__Container-sc-1p0gm8n-0.hcgQjy > .SpinnerOverlay___StyledDiv-sc-ee99c1-0:has(.Spinner__SpinnerComponent-sc-p63ahb-0),
  .FileEditContainer___StyledDiv3-sc-48rzpu-7 > .Fade__Container-sc-1p0gm8n-0 > .SpinnerOverlay___StyledDiv-sc-ee99c1-0 {
    background: color-mix(in hsl, var(--pageBackground) 40%, transparent) !important;
    border-radius: var(--borderRadius) !important;
  }

  /* server list */
  .dyLna-D:hover {
    --tw-border-opacity: 0 !important;
    border-color: var(--pageSecondaryHover);
  }
  .Pagination___StyledDiv-sc-7j9cqz-1 {
    position: fixed;
    z-index: 21 !important;
    bottom: 20px;
    right: 20px;
  }
  .Pagination__Block-sc-7j9cqz-0 {
    border: none !important;
    background-color: var(--pageSecondary);
    box-shadow: var(--pageBackground) 0px 0px 61px 13px;
  }
  .Pagination__Block-sc-7j9cqz-0.cDkCmT, .Pagination__Block-sc-7j9cqz-0.gfhYPP {
    background-color: var(--pagePrimaryHover) !important;
  }
  .Pagination__Block-sc-7j9cqz-0:hover {
    background-color: var(--pageSecondaryHover) !important;
  }

  /* shadows begone */
  .oLbNP, .ekHIsr { --tw-shadow: unset !important; }
  /* pterodactyl home */
  div.ServerRow___StyledDiv-sc-1ibsw91-3.ecJXa-d div.icon.mr-4 {
    background-color: var(--pageSecondaryHover)
  }
  p { color: var(--pagePrimary) }
  /* account 2fa codes */
  #disable-totp-form .form-input.styles-module_S9h-xMSg.styles-module_AWe-iPIe,
  .bg-gray-800.rounded.p-2.mt-6.cursor-pointer
  { background: none; background-color: var(--pageSecondaryHover) !important }
  /* account api key */
  .ApiKeyModal___StyledPre-sc-s6pcab-2.irvYxA,
  .AccountApiContainer___StyledCode-sc-1c4s3nm-10.lnIosn { background-color: var(--pageSecondaryHover) !important }
  /* file manager */
  div.style-module_35MPv1CD.active { margin-bottom: 0px; background-color: var(--pageSecondary); transition: background-color .5s; }
  div.style-module_1WqkLT9X { margin-bottom: 7px; border-radius: var(--borderRadius); background-color: var(--pageSecondary); transition: background-color .5s; }
  div.style-module_35MPv1CD.active:hover { background-color: var(--pageSecondaryHover) }
  div.style-module_1WqkLT9X:hover { background-color: var(--pageSecondaryHover) }

  div[class*="FileObjectRow___StyledDiv-sc"],
  div[class*="FileObjectRow___StyledDiv2-sc"],
  div[class*="FileObjectRow___StyledDiv4-sc"],
  div[class*="FileObjectRow___StyledDiv3-sc"]
  {
    color: var(--pagePrimary);
    opacity: .85;
  }

  [class*="SelectFileCheckbox___StyledLabel-sc"] {
    padding-bottom: 12px;
  }

  .style-module_2vOYXZWm:disabled, .style-module_3kBDV_wo:disabled {
    color: color-mix(in hsl, var(--pagePrimary) 80%, transparent) !important;
  }

  .CodeMirror.cm-s-ayu-mirage.CodeMirror-wrap.CodeMirror-overlayscroll,
  .CodeMirror-gutter.CodeMirror-foldgutter,
  .CodeMirror-gutter.CodeMirror-linenumbers,
  [class*="FileEditContainer___StyledDiv4-sc"] select[class*="Select-sc"] { background-color: var(--pageSecondary); border: none; }

  [class*="Input-sc"][class*="SelectFileCheckBox__FileActionCheckbox-sc"],
  [class*="Input-sc"][type="checkbox"] {
    appearance: none;
    border-radius: 4px;
    cursor: pointer;
    position: relative;
    border: 1px solid #ffffff40;
  }
  [class*="Input-sc"][class*="SelectFileCheckBox__FileActionCheckbox-sc"]:hover,
  [class*="Input-sc"][type="checkbox"]:hover {
    border-color: #ffffff60;
  }

  [class*="Input-sc"][class*="SelectFileCheckBox__FileActionCheckbox-sc"]:checked,
  [class*="Input-sc"][type="checkbox"]:checked {
    border-color: #ddd;
  }

  [class*="Input-sc"][class*="SelectFileCheckBox__FileActionCheckbox-sc"]:checked::before,
  [class*="Input-sc"][type="checkbox"]:checked::before {
    font-size: 14px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  [class*="Input-sc"][class*="SelectFileCheckBox__FileActionCheckbox-sc"]:checked::after,
  [class*="Input-sc"][type="checkbox"]:checked::after {
    font-family: bootstrap-icons !important;
    content: "\F26E";
    color: #000000;
    font-size: 14px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  /* .pteroignore & New Directory Button, new file path -> woo */
  .FileEditContainer___StyledDiv2-sc-48rzpu-2.eyfINz {
    border-radius: var(--borderRadius);
    background-color: var(--pageSecondary);
    border-left: 0px;
  }

  .NewDirectoryButton___StyledSpan2-sc-e7hrah-3.eFSjGD {
    transition: background-color .3s;
    color: var(--pagePrimary);
  }

  /* mass action bar */
  .MassActionsBar___StyledDiv2-sc-1x2nl3g-1.haunnZ.fade-enter-done,
  .MassActionsBar___StyledDiv2-sc-1x2nl3g-1.haunnZ {
    background-color: var(--pageSecondaryHover);
    height: 50px;
    border-radius: var(--borderRadius);
    padding: 10px 10px !important;
  }
  /* mass action bar container */
  div.fixed.bottom-0.mb-6.flex.justify-center.w-full.z-50 {
    width: auto;
    right: 150px;
  }
  /* mass action bar buttons */
  .MassActionsBar___StyledDiv2-sc-1x2nl3g-1 > button:nth-child(1),
  .MassActionsBar___StyledDiv2-sc-1x2nl3g-1 > button:nth-child(2),
  .MassActionsBar___StyledDiv2-sc-1x2nl3g-1 > button:nth-child(3) {
    font-size: 0px;
    margin-top: 10px !important;
    margin-bottom: 10px !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    scale: 0.75 !important;
    border-radius: var(--borderRadius) !important;
    padding-left: 0px !important;
    padding-right: 0px !important;
    background-color: #00000000 !important;
  }

  .MassActionsBar___StyledDiv2-sc-1x2nl3g-1 > button:nth-child(1)::before {
    font-size: 22px;
    color: #c7c9cc;
    font-family: bootstrap-icons !important;
    content: "\F2E2";
    padding-left: 10px;
    padding-right: 10px;
    padding-top: 7px;
    padding-bottom: 7px;
  }
  .MassActionsBar___StyledDiv2-sc-1x2nl3g-1 > button:nth-child(2)::before {
    font-size: 22px;
    color: #c7c9cc;
    font-family: bootstrap-icons !important;
    content: "\F10C";
    padding-left: 10px;
    padding-right: 10px;
    padding-top: 7px;
    padding-bottom: 7px;
  }
  .MassActionsBar___StyledDiv2-sc-1x2nl3g-1 > button:nth-child(3)::before {
    font-size: 22px;
    color: #ff524c;
    font-family: bootstrap-icons !important;
    content: "\F78A";
    padding-left: 10px;
    padding-right: 10px;
    padding-top: 7px;
    padding-bottom: 7px;
  }

  /* checkbox */
  [class*="Input-sc"][type="checkbox"]:checked, [class*="Input-sc"][type="radio"]:checked,
  .jqTCDz[type="checkbox"]:checked, .jqTCDz[type="radio"]:checked {
    background: hex(#3a3a3a00) !important;
    background-color: #dddddd !important;
    background-image: none;
    border: none;
    border-color: #00000000;
  }
  .jqTCDz[type="checkbox"],
  .jqTCDz[type="radio"],
  [class*="Input-sc"][type="checkbox"],
  [class*="Input-sc"][type="radio"] {
    background-color: transparent !important;
    border-color: #ffffff30;
    transition: border-color .3s, background-color .3s;
    border-radius: 5px;
  }
  .jqTCDz[type="checkbox"]:hover,
  .jqTCDz[type="radio"]:hover,
  [class*="Input-sc"][type="checkbox"]:hover,
  [class*="Input-sc"][type="radio"]:hover {
    border-color: #ffffffaa;
    transition: border-color .3s, background-color .3s
  }

  /* terminal */
  div.style-module_2Vp6MaXq > div.style-module_1DtraXMW:has(.Icon___StyledSvg-sc-omsq29-0) {
    backdrop-filter: unset !important;
    background-color: transparent !important;
    box-shadow: unset !important;
    border: unset !important;
    border-bottom: unset !important;
  }
  div.style-module_2Vp6MaXq {
    background-color: var(--pageSecondary);
    overflow: hidden;
    color: #fff !important;
  }
  div.style-module_2XbmHEcn {
    background-color: var(--pageSecondary);
  }
  .style-module_RcP2_Fvj .style-module_3TDo--Tn:focus {
    border-color: var(--pagePrimaryHover) !important;
  }

  /* graphs */
  div.style-module_2XbmHEcn:has(div > canvas) {
    border-bottom-color: var(--pageSecondary) !important;
    box-shadow: unset !important;
  }


  .xterm-screen + div:has(.svg-inline--fa.fa-bell) {
    position: absolute !important;
    right: 0.5rem !important;
    bottom: 0.5rem !important;
    padding: 0.5rem !important;
    font-size: 1.25em !important;
    box-shadow: var(--pageBackground) 0px 0px 27px 0px !important;
    background-color: var(--pageSecondaryHover) !important;
    z-index: 999 !important;
    cursor: pointer !important;
    visibility: visible !important;
    border-radius: 50px !important;
    width: 39px !important;
    padding-left: 12px !important;
    padding-top: 11px !important;
  }

  .xterm-search-bar__addon {
    position: absolute;
    max-width: 1467px;
    top: 0.5rem;
    right: 0.5rem;
    color: #000;
    box-shadow: var(--pageBackground) 0px 0px 27px 0px;
    background-color: var(--pageSecondaryHover);
    z-index: 999;
    display: flex;
    border-radius: 100px;
    padding: 8px;
  }
  .xterm-search-bar__addon .search-bar__input {
    background-color: color-mix(in hsl, var(--pageSecondaryActive) 96%, white);
    color: #ccc;
    border: 0;
    padding: 2px;
    height: 20px;
    width: 227px;
    border-radius: 100px;
  }
  .xterm-search-bar__addon .search-bar__input:focus-visible { outline: unset !important }
  .xterm-search-bar__addon .search-bar__btn {
    background-color: color-mix(in hsl, var(--pageSecondaryActive) 96%, white);
    border-radius: 100px;
    transition: background-color .2s;
  }
  .xterm-search-bar__addon .search-bar__btn.prev {
    margin-left: 6px !important;
    border-radius: 20px 0px 0px 20px !important;
  }
  .xterm-search-bar__addon .search-bar__btn.next {
    margin-left: 0px !important;
    border-radius: 0px 20px 20px 0px !important;
  }
  .xterm-search-bar__addon .search-bar__btn.close {
    margin-left: 6px !important;
  }
  .xterm-search-bar__addon .search-bar__btn:hover {
    background-color: color-mix(in hsl, var(--pageSecondaryActive) 86%, white);
  }

  /* copy notification */
  div.rounded-md.py-3.px-4.text-gray-200.bg-neutral-600\/95.shadow {
    background-color: var(--pageSecondary) !important;
  }


  /* ICONS INSTEAD OF TEXT ON START/STOP/RESTART BUTTONS */
  [class*="ContentContainer-sc"] > div.grid.grid-cols-4 > div + div.self-end > div.flex > button[class*="style-module"] {
    font-size: 0px !important;
    background-color: transparent !important;
    transition: background-color .3s !important;
    border: none !important;
    box-shadow: none !important;
    width: 33.3% !important;
    flex: unset !important;
    border-radius: 100px !important;
  }
  @if($n_server_colored_power)
    #power-start { background-color: var(--statusOnline) !important; }
    #power-restart { background-color: var(--statusStarting) !important; }
    #power-stop { background-color: var(--statusError) !important; }

    #power-start:hover { background-color: color-mix(in hsl, var(--statusOnline) 95%, white) !important; }
    #power-restart:hover { background-color: color-mix(in hsl, var(--statusStarting) 95%, white) !important; }
    #power-stop:hover { background-color: color-mix(in hsl, var(--statusError) 95%, white) !important; }
  @else
    #power-start,
    #power-restart,
    #power-stop {
      background-color: var(--pageSecondary) !important;
    }

    #power-start:hover,
    #power-restart:hover,
    #power-stop:hover
      background-color: var(--pageSecondaryHover) !important;
    }
  @endif
  {}
  #power-start::before {
    font-family: bootstrap-icons !important;
    font-size: 25px !important;
    content: "\F4F4" !important;
  }
  #power-restart::before {
    font-family: bootstrap-icons !important;
    font-size: 21px !important;
    content: "\F130" !important;
  }
  #power-stop::before {
    font-family: bootstrap-icons !important;
    font-size: 25px !important;
    content: "\F592" !important;
  }


  /* default status */
  .Icon___StyledSvg-sc-omsq29-0.ejRaBu.text-gray-100,
  /* warning status */
  .style-module_1DtraXMW.bg-yellow-500 .Icon___StyledSvg-sc-omsq29-0.ejRaBu.text-gray-50,
  /* danger status */
  .style-module_1DtraXMW.bg-red-500 .Icon___StyledSvg-sc-omsq29-0.ejRaBu.text-gray-50 {
    /* styles */
    z-index: 4;
    margin-right:25px;
    scale: 4;
    color: #ffffff12 !important;
    transition: color .3s, scale .3s, background-color .3s;
  }

  .Icon___StyledSvg-sc-omsq29-0.ejRaBu.text-gray-100:hover {
    z-index: 4;
    color: #ffffff18 !important;
  }

  /* address */ div.style-module_2Vp6MaXq:nth-child(1) > div:nth-child(2) > svg:nth-child(1) {scale: 5 !important;rotate: -20deg !important;}
  /* address hover */ div.style-module_2Vp6MaXq:nth-child(1):hover > div:nth-child(2) > svg:nth-child(1) {scale: 5.3 !important; color: #ffffff18 !important;}

  /* uptime */ div.style-module_2Vp6MaXq:nth-child(2) > div:nth-child(2) > svg:nth-child(1) {scale: 4.5 !important;rotate: -20deg !important; color: #ffffff12 !important; transition: color .3s, scale .3s, background-color .3s;}
  /* uptime hover */ div.style-module_2Vp6MaXq:nth-child(2):hover > div:nth-child(2) > svg:nth-child(1) {scale: 4.8 !important; color: #ffffff18 !important;}

  /* cpu */ div.style-module_2Vp6MaXq:nth-child(3) > div:nth-child(2) > svg:nth-child(1) {scale: 3.8 !important;rotate: -20deg !important;}
  /* cpu hover */ div.style-module_2Vp6MaXq:nth-child(3):hover > div:nth-child(2) > svg:nth-child(1) {scale: 4.1 !important; color: #ffffff18 !important;}

  /* ram */ div.style-module_2Vp6MaXq:nth-child(4) > div:nth-child(2) > svg:nth-child(1) {scale: 4 !important;rotate: -20deg !important;}
  /* ram hover */ div.style-module_2Vp6MaXq:nth-child(4):hover > div:nth-child(2) > svg:nth-child(1) {scale: 4.3 !important; color: #ffffff18 !important;}

  /* disk */ div.style-module_2Vp6MaXq:nth-child(5) > div:nth-child(2) > svg:nth-child(1) {scale: 4.5 !important;rotate: -20deg !important;}
  /* disk hover */ div.style-module_2Vp6MaXq:nth-child(5):hover > div:nth-child(2) > svg:nth-child(1) {scale: 4.8 !important; color: #ffffff18 !important;}

  /* network in */ div.style-module_2Vp6MaXq:nth-child(6) > div:nth-child(2) > svg:nth-child(1) {scale: 4.5 !important;rotate: -20deg !important;}
  /* network in hover */ div.style-module_2Vp6MaXq:nth-child(6):hover > div:nth-child(2) > svg:nth-child(1) {scale: 4.8 !important; color: #ffffff18 !important;}

  /* network out */ div.style-module_2Vp6MaXq:nth-child(7) > div:nth-child(2) > svg:nth-child(1) {scale: 4.5 !important;rotate: -20deg !important;}
  /* network out hover */ div.style-module_2Vp6MaXq:nth-child(7):hover > div:nth-child(2) > svg:nth-child(1) {scale: 4.8 !important; color: #ffffff18 !important;}

  /* xterm */
  .relative.style-module_1AMtO9lt input { background: none; background-color: var(--pageSecondaryHover); }
  .style-module_1n_DiqmT.style-module_1AMtO9lt, .xterm-screen, .xterm-viewport, .terminal.xterm { background: none !important; background-color: var(--pageSecondary) !important; }

  /* schedules */
  .GreyRowBox-sc-1xo9c6v-0,
  .flex.ScheduleEditContainer___StyledScheduleCronRow-sc-1fhsmlc-5.gLoyLu { background-color: var(--pageSecondary) }
  div.ScheduleEditContainer___StyledDiv3-sc-1fhsmlc-7 { background-color: var(--pageSecondary); border-bottom: unset !important; margin-bottom: 1rem; }
  div.ScheduleEditContainer___StyledDiv-sc-1fhsmlc-0.bWXUsX { background-color: var(--pageSecondary) }
  div.ScheduleTaskRow___StyledDiv-sc-17r38ls-0.ipThkt { background: none !important; background-color: var(--pageSecondary) !important; border: none; margin-bottom: 5px; border-radius: 2.5px }
  .ScheduleTaskRow___StyledDiv4-sc-17r38ls-6.cPagWz { background-color: var(--pageSecondaryHover); }
  .ScheduleEditContainer___StyledDiv7-sc-1fhsmlc-18 { background: none !important; }

  div.EditScheduleModal___StyledDiv2-sc-wh9db9-4,
  div.EditScheduleModal___StyledDiv4-sc-wh9db9-6,
  div.EditScheduleModal___StyledDiv5-sc-wh9db9-7,
  div.TaskDetailsModal___StyledDiv5-sc-1b5dnyw-7 { background-color: var(--pageSecondaryHover); border: none; } /*create/edit schedule*/

  div.EditScheduleModal___StyledDiv3-sc-wh9db9-5.hsBdEm,
  div.EditScheduleModal___StyledDiv3-sc-wh9db9-5.hsBdEm * {
    background-color: var(--pageSecondaryActive) !important;
    border-radius: var(--borderRadius) !important;
  }
  div.EditScheduleModal___StyledDiv3-sc-wh9db9-5.hsBdEm {
    margin-top: 15px;
  }
  .jpYRCT { box-shadow: none !important; }

  /* settings */
  .jRrWLs { border-bottom-width: 0 !important; }
  .SettingsContainer___StyledTitledGreyBox-sc-1e5ycmz-3 > div:nth-child(2) > div:nth-child(1) > input:nth-child(2) { background-color: var(--pageSecondaryActive); border: none; }
  .SettingsContainer___StyledDiv3-sc-1e5ycmz-4.keyYci [class*="Input-sc"] { background-color: var(--pageSecondaryActive); border: none; }
  #name { background-color: var(--pageSecondaryActive); border: none; }
  .Input__Textarea-sc-19rce1w-1 { background-color: var(--pageSecondaryActive); border: none; }

  div.RenameServerBox___StyledTitledGreyBox-sc-1bh2mfg-0 > div:nth-child(1),
  div.SettingsContainer___StyledTitledGreyBox2-sc-1e5ycmz-10 > div:nth-child(1),
  div.ReinstallServerBox___StyledTitledGreyBox-sc-1prmksw-0 > div:nth-child(1),
  div.SettingsContainer___StyledTitledGreyBox-sc-1e5ycmz-3 > div:nth-child(1)
  { background-color: var(--pageSecondary); border-bottom-width: 0 !important; }

  .SettingsContainer___StyledCode-sc-1e5ycmz-12.EwfIk,
  .SettingsContainer___StyledCode2-sc-1e5ycmz-14.izAmwE,
  div.RenameServerBox___StyledTitledGreyBox-sc-1bh2mfg-0 > div:nth-child(2),
  div.SettingsContainer___StyledTitledGreyBox2-sc-1e5ycmz-10 > div:nth-child(2),
  div.ReinstallServerBox___StyledTitledGreyBox-sc-1prmksw-0 > div:nth-child(2),
  div.SettingsContainer___StyledTitledGreyBox-sc-1e5ycmz-3 > div:nth-child(2),
  .oLbNP,
  .StartupContainer___StyledDiv-sc-163imy2-0.dUOPLC .TitledGreyBox___StyledDiv3-sc-gvsoy-4.fKIIIQ,
  .StartupContainer___StyledDiv-sc-163imy2-0.dUOPLC .TitledGreyBox___StyledDiv2-sc-gvsoy-1.jRrWLs,
  .StartupContainer___StyledDiv-sc-163imy2-0.dUOPLC .TitledGreyBox___StyledDiv-sc-gvsoy-0.oLbNP.StartupContainer___StyledTitledGreyBox-sc-163imy2-1.kRunTE,
  .StartupContainer___StyledDiv-sc-163imy2-0.dUOPLC .TitledGreyBox___StyledDiv-sc-gvsoy-0.oLbNP.StartupContainer___StyledTitledGreyBox2-sc-163imy2-4.aRhRz,
  .StartupContainer___StyledDiv3-sc-163imy2-8.gMWjQt .TitledGreyBox___StyledDiv2-sc-gvsoy-1.jRrWLs,
  .StartupContainer___StyledDiv3-sc-163imy2-8.gMWjQt .TitledGreyBox___StyledDiv3-sc-gvsoy-4.fKIIIQ,
  .StartupContainer___StyledDiv3-sc-163imy2-8.gMWjQt .TitledGreyBox___StyledDiv-sc-gvsoy-0.oLbNP
  { background-color: var(--pageSecondary) }

  .SettingsContainer___StyledDiv6-sc-1e5ycmz-7.bzLcIU {
    border-radius: var(--borderRadius);
    border-left: 0px;
  }

  .EditSubuserModal___StyledPermissionTitleBox-sc-1hon03w-8,
  .EditSubuserModal___StyledPermissionTitleBox-sc-1hon03w-8 > .TitledGreyBox___StyledDiv2-sc-gvsoy-1 {
    background-color: var(--pageSecondaryHover)
  }

  /* activity  ACCOUNT + SERVER */
  .ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk > div.bg-gray-700 > div.grid {
    background-color: var(--pageSecondary);
    border-radius: var(--borderRadius);
    border: none;
    margin-bottom: 10px;
  }
  div.bg-gray-700:has(div.grid.grid-cols-10.py-4.border-b-2.border-gray-800.last\:rounded-b.last\:border-0.group),
  div.ContentContainer-sc-x3r2dw-0:nth-child(2) {
    background: none;
    background-color: none;
    transition: none;
    animation: none;
    border: none;
    border-color: #00000000;
  }
  /* startup */
  [class*="StartupContainer___StyledDiv-sc"] [class*="TitledGreyBox___StyledDiv-sc"][class*="StartupContainer___StyledTitledGreyBox"] [class*="TitledGreyBox___StyledDiv"] [class*="Select-sc"],
  .StartupContainer___StyledDiv-sc-163imy2-0.dUOPLC .TitledGreyBox___StyledDiv-sc-gvsoy-0.oLbNP.StartupContainer___StyledTitledGreyBox2-sc-163imy2-4.aRhRz .TitledGreyBox___StyledDiv3-sc-gvsoy-4.fKIIIQ .Select-sc-17exaqp-0.dupyoa,
  .StartupContainer___StyledDiv2-sc-163imy2-2.gMdcgi p,
  [class*="Input-sc"] {
    background-color: var(--pageSecondaryActive);
    border: none;
  }
  [class*="Input-sc"]:not([type="checkbox"]),
  [class*="StartupContainer___Styled"] [class*="Input-sc"] {
    background-color: var(--pageSecondaryActive) !important;
  }

  .Label-sc-g780ms-0.eDArzA,
  .StartupContainer___StyledDiv3-sc-163imy2-8.gMWjQt .Input-sc-19rce1w-0.jqTCDz { border: none; }
  /* subusers */
  .TitledGreyBox___StyledDiv3-sc-gvsoy-4.fKIIIQ .PermissionRow__Container-sc-1h899cn-0.icxFlO:hover { border-color: #00000000; border: none; background-color: var(--pageSecondaryActive) }
  .TitledGreyBox___StyledDiv3-sc-gvsoy-4.fKIIIQ .PermissionRow__Container-sc-1h899cn-0.icxFlO { border-color: #00000000; border: none; }
  /* backups */
  .CreateBackupButton___StyledDiv2-sc-da8bqw-3.eDncUf { background-color: var(--pageSecondaryHover); border: none; }

  /* databases */
  [class*="Button__ButtonStyle-sc"][color="red"],
  [class*="Button__ButtonStyle-sc"].kztmyO,
  [class*="Button__ButtonStyle-sc"].jUQpfY {
    border: unset;
    background-color: var(--pageSecondaryHover) !important;
  }
  [class*="Button__ButtonStyle-sc"][color="red"]:hover,
  [class*="Button__ButtonStyle-sc"].kztmyO:hover,
  [class*="Button__ButtonStyle-sc"].jUQpfY:hover { background-color: var(--pageSecondaryActive) !important; }

  [class*="DatabaseRow___StyledP3-sc"],
  [class*="DatabaseRow___StyledP5-sc"],
  [class*="DatabaseRow___StyledP7-sc"] {
    background-color: var(--pageSecondaryActive);
    padding: 4px 10px;
    padding-top: 5px !important;
    border-radius: var(--borderRadius);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
    font-size: 0.875rem !important;
    line-height: 1.25rem !important;
  }
</style>
