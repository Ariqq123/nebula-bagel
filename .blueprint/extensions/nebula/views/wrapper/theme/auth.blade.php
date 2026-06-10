@if(Auth::check() != true)
<script>
  document.querySelector("head > meta[name='theme-color'][content='#0e4688']").setAttribute("content", "{{ $n_palette_auth_1 }}")
</script>
<!-- PTERODACTYL AUTHENTICATION THEMING -->
<style id="nebula-authentication-theme">
  html, body {
    background-color: #000 !important;
  }
  div.[class=*"ProgressBar___StyledDiv-sc"] { position: fixed; z-index: 4; top: 0; left: 0 !important; width: 100% !important; }

  @keyframes backdrop {
    0%   {scale: calc(.1 +@if($n_auth_background_appearance == "1") 2.4 @else 1 @endif)}
    100% {scale: @if($n_auth_background_appearance == "1") 2 @else 1 @endif}
  }

  @if($n_auth_customlogo != "")
    [class*="LoginFormContainer__Container-sc"] [class*="LoginFormContainer___StyledH-sc"] {
      content: url("{{ $n_auth_customlogo }}");
      border-radius: 10px;
      padding: 0;
      height: 65px;
      max-width: 100%;
      margin-left: auto;
      margin-right: auto;
    }
  @endif

  [class*="LoginFormContainer__Container-sc"] [class*="LoginFormContainer___StyledH-sc"] {
    padding-bottom: .5rem !important;
    padding-top: unset !important;
  }

  .nebula-auth-wallpaper {
    z-index: 3;
    overflow: hidden;
    @if($n_auth_background_image == "")background-color: var(--authA);
    @else background: url("{{ $n_auth_background_image }}") no-repeat; background-color: #000;@endif
    background-position: center;
    background-size: cover;
    height: 100vh;
    width: 100vw;
    top: 0; left: 0;
    position: fixed;
    @if($n_auth_background_appearance == "1")filter: blur(50px);scale: 2;@endif
    @if($n_auth_background_appearance == "1")filter: blur(50px);@endif
    @if($n_auth_background_appearance == "2")opacity: 0.6;@endif
    animation: backdrop 2s;
  }
  .nebula-auth-backdrop {
    background-color: #000 !important;
    z-index: 2;
    position: fixed;
    left: 0; top: 0;
    width: 100vw; height: 100vh;
  }

  div.App___StyledDiv-sc-2l91w7-0.fnfeQw { background-color: var(--pageBackground) !important; z-index: 1; }
  .LoginFormContainer___StyledDiv-sc-cyh04c-3 { padding-left: 10px !important; padding-right: 10px !important; }

  /* login container */
  div.LoginFormContainer__Container-sc-cyh04c-0 {
    z-index: 4;
    position: fixed;
    width: 30%;
    left: 50%;
    background-color: var(--authB);
    border-radius: var(--borderRadiusAuth);
    top: 50%;
    -ms-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
    padding: 40px !important;
  }
  @media screen and (max-width: 1200px) {div.LoginFormContainer__Container-sc-cyh04c-0 {width: 40%;}}
  @media screen and (max-width: 1100px) {div.LoginFormContainer__Container-sc-cyh04c-0 {width: 45%;}}
  @media screen and (max-width: 1000px) {div.LoginFormContainer__Container-sc-cyh04c-0 {width: 50%;}}
  @media screen and (max-width: 900px) {div.LoginFormContainer__Container-sc-cyh04c-0 {width: 55%;}}
  @media screen and (max-width: 800px) {div.LoginFormContainer__Container-sc-cyh04c-0 {width: 60%; padding: 30px !important;}}
  @media screen and (max-width: 700px) {div.LoginFormContainer__Container-sc-cyh04c-0 {width: 70%; padding: 25px !important;}}
  @media screen and (max-width: 600px) {div.LoginFormContainer__Container-sc-cyh04c-0 {width: 80%; padding: 20px !important;}}
  @media screen and (max-width: 500px) {div.LoginFormContainer__Container-sc-cyh04c-0 {width: 90%; padding: 15px !important;}}


  .LoginFormContainer___StyledDiv-sc-cyh04c-3 { background: none; background-color: #00000000; box-shadow: none; }
  div.LoginFormContainer___StyledDiv2-sc-cyh04c-4 { display: none; }
  [class*="Input-sc"] {
    background: none !important;
    background-color: var(--authC) !important;
    border: none;
    border-bottom: 5px var(--authD) solid !important;
    border-radius: 0;
  }
  input[type=text],input[type=text]::placeholder,
  input[type=password],input[type=password]::placeholder,
  input[type=email],input[type=email]::placeholder{
    color:white !important;
  }

  [class*="Button__ButtonStyle-sc"]:not(:disabled),
  [class*="Button__ButtonStyle-sc"] {
    background: none !important;
    background-color: var(--authF) !important;
    transition: opacity .2s;
    border: none !important;
  }
  [class*="Button__ButtonStyle-sc"]:hover:not(:disabled) {
    background-color: var(--authF) !important;
    opacity: 0.90;
  }

  /* le button */
  [class*="Button__ButtonStyle-sc"] [class*="Button___StyledSpan-sc"] {
    color: var(--authH) !important;
  }
  .jtfgdV {
    border-color: var(--authH) color-mix(in hsl, var(--authH) 20%, transparent) color-mix(in hsl, var(--authH) 20%, transparent) !important;
  }

  /*ptero footer*/
  .LoginFormContainer___StyledP-sc-cyh04c-7.llNNfK {
    padding: 0;
    margin: 0;
  }
  .LoginFormContainer___StyledP-sc-cyh04c-7.llNNfK,
  .LoginFormContainer___StyledP-sc-cyh04c-7.llNNfK > * {
    opacity: 1 !important;
    color: var(--authG) !important;
  }



  .cjgCjC {
    color: #606060 !important;
  }
  .dqkKHi,
  .LoginContainer___StyledLink-sc-qtrnpk-4.cjgCjC {
    color: color-mix(in hsl, var(--authG) 90%, white) !important;
    transition: color .2s !important;
  }
  .LoginContainer___StyledLink-sc-qtrnpk-4.cjgCjC:hover {
    color: color-mix(in hsl, var(--authG) 90%, white) !important;
  }

  .input-help.error, div:has(.input-help.error) > [class*="Label-sc"] {
    color: var(--authE) !important;
  }
  [class*="Input-sc"]:user-invalid, .hSTwlB {
    border-color: var(--authE) !important;
  }

</style>
@endif
