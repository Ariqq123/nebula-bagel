function saveAction() {
  document.getElementById("submit").click()
}
function element(selector) { return document.querySelector(selector) }
function showSaveButton() {
  unsaved=true
  document.querySelector(".save-button").style.bottom = "var(--bottom)"
  document.querySelector(".save-padding").style.height = "var(--height)"
}

unsaved=false
document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("editor-form").addEventListener("change", function () { showSaveButton() });

  // input highlighting
  document.querySelectorAll('.option-input').forEach(function(input) {
    input.addEventListener('focus', function() {
      this.parentNode.querySelector('.option-icon').classList.add('active');
    });
    input.addEventListener('blur', function() {
      this.parentNode.querySelector('.option-icon').classList.remove('active');
    });
  });

  const params = new URL(window.location).searchParams;
  if(params.get('source') == "panel"){
    element("input[type='hidden'][name='_endpoint']").value = window.location.pathname+"?source=panel"
  }
});

function modal(selector) {
  let modal = document.querySelector(selector)
  let container = document.querySelector(".editor-container")

  container.style.position = "relative";
  container.style.left = 0;
  setTimeout(() => {
    container.style.left = "-40px";
    container.style.opacity = 0;
  }, 30);
  setTimeout(() => {
    container.style.display = "none";
    modal.style.display = "block";
    modal.style.position = "relative";
    modal.style.right = "-40px";
    setTimeout(() => {
      modal.style.right = "0";
      modal.style.opacity = 1;
    }, 30);
  }, 300);
}

function closeModal(selector) {
  let modal = document.querySelector(selector)
  let container = document.querySelector(".editor-container")

  modal.style.position = "relative";
  modal.style.right = 0;
  setTimeout(() => {
    modal.style.right = "-40px";
    modal.style.opacity = 0;
  }, 15);
  setTimeout(() => {
    modal.style.display = "none";
    container.style.display = "block";
    container.style.position = "relative";
    container.style.left = "-40px";
    setTimeout(() => {
      container.style.left = "0";
      container.style.opacity = 1;
    }, 15);
  }, 300);

  setTimeout(() => {
    let properties = ["position", "right", "opacity", "display", "left"];
    for (let i = 0; i < properties.length; i++) {
      modal.style[properties[i]] = null;
      container.style[properties[i]] = null;
    }
  }, 515)
}