function HandleNebulaError(e,t){return document.getElementById("app").innerHTML=`
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Inter&display=swap');
    </style>
    <div style="
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: #040814;
      z-index: 99999999;
    ">
      <div style="
        width: calc(100% - 80px);
        height: calc(100% - 150px);
        padding: 40px;
        position: fixed;
        color: white;
        font-family: 'Inter', sans-serif;
        overflow-y: scroll;
      ">
        <img src="/extensions/nebula/libraries/assets/emma.png" height="45.1333" style="padding-bottom: 12px"/>
        <p style="max-width: 750px">
          ${e}
        </p>
      </div>


      <div style="
        position: fixed;
        right: 40px;
        bottom: 40px;
        color: white;
        font-family: 'Inter', sans-serif;
      ">
        <p style="margin: 0px">
          <a href="https://github.com/prplwtf/nebula/issues/new" style="color: #7997ff">Feedback</a>
          <i class="bi bi-dot"></i>
          <a href="https://nebula.style" style="color: #7997ff">Nebula</a>
        </p>
      </div>


      <div style="
        position: fixed;
        left: 40px;
        bottom: 40px;
        color: white;
        font-family: 'Inter', sans-serif;
      ">
        <p style="margin: 0px">
          <code>( ._.) ${t||""}</code>
        </p>
      </div>
    </div>
  `}console.log("nebula#~ errorHandler.js"),window.addEventListener("DOMContentLoaded",()=>{document.getElementById("app").innerHTML||(document.querySelector(".initialize-notif")&&document.querySelector(".initialize-notif").remove(),document.querySelector(".init-error-bg")&&document.querySelector(".init-error-bg").remove(),document.querySelector(".init-error-style")&&document.querySelector(".init-error-style").remove(),HandleNebulaError(`
      Nebula was unable to initialize because it couldn't access the
      <code>app</code>
      node.
      This usually indicates a failed webpack build and is usually
      indicated by a blank page on stock Pterodactyl instances.

      Administrators might be able to identify the exact problem by
      running
      <code>blueprint -rerun-install</code>
      and watching for errors.

      <br/><br/>

      Please note that this error is commonly
      <b>not caused by Nebula</b>
      due to the theme being a shell around Pterodactyl and doesn't
      modify any part of it's bundle, hence why we're able to show
      this error to you.
    `,"INITIALIZATION_FAILED"))});