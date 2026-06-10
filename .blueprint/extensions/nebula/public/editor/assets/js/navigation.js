let SPINNER = '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><style>.spinner_aj0A{transform-origin:center;animation:spinner_KYSC .75s infinite linear}@keyframes spinner_KYSC{100%{transform:rotate(360deg)}}</style><path d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z" class="spinner_aj0A"/></svg>'
let SPINNER_SM = '<svg width="13.3" height="13.3" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><style>.spinner_aj0A{transform-origin:center;animation:spinner_KYSC .75s infinite linear}@keyframes spinner_KYSC{100%{transform:rotate(360deg)}}</style><path d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z" class="spinner_aj0A"/></svg>'
nonav=false;
cachedPage=null;
forceNav=false;

function navigationAction(page) {
  const params = new URL(window.location).searchParams;
  if((!unsaved && !nonav) || (forceNav == true)) {
    document.querySelectorAll(".fade").forEach(element => {
      element.classList.add("fade-exit")
    });

    if(page == "admin") { 
      nonav=true;
      if(forceNav != true){
        element("button[to='admin']").innerHTML = SPINNER;
      }
      if(params.get('source') == "panel") {
        window.location = "/";
      } else {
        window.location = "/admin/extensions";
      }
    }
    if(page == "general") { nonav=true; if(forceNav != true){element("button[to='general']").innerHTML = SPINNER;} if(params.get('source') == "panel"){return window.location = "/extensions/nebula/editor/editor.php?source=panel";} window.location = "/extensions/nebula/editor/editor.php"; }
    if(page == "palette") { nonav=true; if(forceNav != true){element("button[to='palette']").innerHTML = SPINNER;} if(params.get('source') == "panel"){return window.location = "/extensions/nebula/editor/edit/palette.php?source=panel";} window.location = "/extensions/nebula/editor/edit/palette.php"; }
    if(page == "sidebar") { nonav=true; if(forceNav != true){element("button[to='sidebar']").innerHTML = SPINNER;} if(params.get('source') == "panel"){return window.location = "/extensions/nebula/editor/edit/sidebar.php?source=panel";} window.location = "/extensions/nebula/editor/edit/sidebar.php"; }
    if(page == "dashboard") { nonav=true; if(forceNav != true){element("button[to='dashboard']").innerHTML = SPINNER;} if(params.get('source') == "panel"){return window.location = "/extensions/nebula/editor/edit/dashboard.php?source=panel";} window.location = "/extensions/nebula/editor/edit/dashboard.php"; }
    if(page == "authentication") { nonav=true; if(forceNav != true){element("button[to='authentication']").innerHTML = SPINNER;} if(params.get('source') == "panel"){return window.location = "/extensions/nebula/editor/edit/authentication.php?source=panel";} window.location = "/extensions/nebula/editor/edit/authentication.php"; }
    if(page == "gallery") { nonav=true; if(forceNav != true){element("button[to='gallery']").innerHTML = SPINNER;} if(params.get('source') == "panel"){return window.location = "/extensions/nebula/editor/edit/gallery.php?source=panel";} window.location = "/extensions/nebula/editor/edit/gallery.php"; }
    if(page == "more") { nonav=true; if(forceNav != true){element("button[to='more']").innerHTML = SPINNER;} if(params.get('source') == "panel"){return window.location = "/extensions/nebula/editor/edit/more.php?source=panel";} window.location = "/extensions/nebula/editor/edit/more.php"; }
  }
  if(unsaved && (forceNav == false)) {
    cachedPage=page;
    notify("#notif-unsaved")
  }
}

function notify(elem) {
  let Parent = element(elem)
  let Container = element(elem+"> .notif-container")
  let Hitbox = element(elem+"> .notif-hitbox")

  Parent.style.display = "block"
  if(elem == "#notify-unsaved") { Hitbox.onclick = (() => {if(forceNav != true) { cachedPage=null; }; closeNotify('#notif-unsaved')}); }
  else { Hitbox.onclick = (() => {closeNotify(`${elem}`)}) }
  if(element(elem+"> .notif-container > .button-close") != undefined) {
    if(elem == "#notify-unsaved") { element(elem+"> .notif-container > .button-close").onclick = (() => {if(forceNav != true) { cachedPage=null; }; closeNotify('#notif-unsaved')}); }
    else { element(elem+"> .notif-container > .button-close").onclick = (() => {closeNotify(`${elem}`)}) }
  }
  setTimeout(() => {
    Parent.style.opacity = 1
    setTimeout(() => {
      Container.style.opacity = 1
      Container.style.setProperty("--verticalOffset", "0px")
    }, 100) //200
  }, 30)
}

function closeNotify(elem) {
  let Parent = element(elem)
  let Container = element(elem+"> .notif-container")

  Parent.style.opacity = null
  Container.style.opacity = null
  Container.style.setProperty("--verticalOffset", "30px")
  setTimeout(() => {
    Parent.style.display = null
  }, 800)
}

function saveUnsaved() {
  forceNav=true;
  element("#unsaved-save").innerHTML = "<span style='opacity:0'>.</span>"+SPINNER_SM+"<span style='opacity:0'>.</span>";
  element("#unsaved-discard").style.opacity = ".6"
  element("#unsaved-discard").disabled = true
  element("#unsaved-cancel").style.opacity = ".6"
  element("#unsaved-cancel").disabled = true
  element("#notif-unsaved > .notif-hitbox").onclick = undefined
  //element("#unsaved").style.setProperty("--navOpacity", "0")
  setTimeout(() => {
    if(cachedPage == "admin") { element("input[name='_endpoint']").value = "/admin/extensions"; element(".save-button").click() }
    if(cachedPage == "general") { element("input[name='_endpoint']").value = "/extensions/nebula/editor/editor.php"; element(".save-button").click() }
    if(cachedPage == "palette") { element("input[name='_endpoint']").value = "/extensions/nebula/editor/edit/palette.php"; element(".save-button").click() }
    if(cachedPage == "sidebar") { element("input[name='_endpoint']").value = "/extensions/nebula/editor/edit/sidebar.php"; element(".save-button").click() }
    if(cachedPage == "dashboard") { element("input[name='_endpoint']").value = "/extensions/nebula/editor/edit/dashboard.php"; element(".save-button").click() }
    if(cachedPage == "authentication") { element("input[name='_endpoint']").value = "/extensions/nebula/editor/edit/authentication.php"; element(".save-button").click() }
    if(cachedPage == "more") { element("input[name='_endpoint']").value = "/extensions/nebula/editor/edit/more.php"; element(".save-button").click() }
  }, 100)
}
function discardUnsaved() {
  forceNav=true;
  element("#unsaved-discard").innerHTML = "<span style='opacity:0'>.</span>"+SPINNER_SM+"<span style='opacity:0'>.</span>";
  element("#unsaved-save").style.opacity = ".6"
  element("#unsaved-save").disabled = true
  element("#unsaved-cancel").style.opacity = ".6"
  element("#unsaved-cancel").disabled = true
  element("#notif-unsaved > .notif-hitbox").onclick = undefined
  //element("#unsaved").style.setProperty("--navOpacity", "0")
  setTimeout(() => {
    navigationAction(cachedPage);
  }, 100)
}

document.addEventListener("DOMContentLoaded", function (event) {
  element(".save-button").addEventListener("click", function () {
    if(forceNav != true) {
      element(".save-button").innerHTML = SPINNER;
      document.querySelectorAll(".fade").forEach(element => {
        element.classList.add("fade-exit")
      });
    }
  });
});