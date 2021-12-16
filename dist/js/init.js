(() => {
  function ginInitDarkmode() {
    1 == localStorage.getItem("Drupal.gin.darkmode") || "auto" === localStorage.getItem("Drupal.gin.darkmode") && window.matchMedia("(prefers-color-scheme: dark)").matches ? document.documentElement.classList.add("gin--dark-mode") : !0 === document.documentElement.classList.contains("gin--dark-mode") && document.documentElement.classList.remove("gin--dark-mode");
  }
  if (localStorage.getItem("GinDarkMode") && (localStorage.setItem("Drupal.gin.darkmode", localStorage.getItem("GinDarkMode")), 
  localStorage.removeItem("GinDarkMode")), localStorage.getItem("GinSidebarOpen") && (localStorage.setItem("Drupal.gin.sidebarExpanded", localStorage.getItem("GinSidebarOpen")), 
  localStorage.removeItem("GinSidebarOpen")), localStorage.getItem("GinAccentColorCustom") && (localStorage.setItem("Drupal.gin.customAccentColor", localStorage.getItem("GinAccentColorCustom")), 
  localStorage.removeItem("GinAccentColorCustom")), ginInitDarkmode(), window.addEventListener("DOMContentLoaded", (function() {
    localStorage.getItem("Drupal.gin.darkmode") || (localStorage.setItem("Drupal.gin.darkmode", drupalSettings.gin.darkmode), 
    ginInitDarkmode());
  })), localStorage.getItem("Drupal.gin.sidebarExpanded")) {
    var style = document.createElement("style"), className = "gin-toolbar-inline-styles";
    if (style.className = className, "true" === localStorage.getItem("Drupal.gin.sidebarExpanded")) {
      style.innerHTML = "\n    @media (min-width: 976px) {\n      body.gin--vertical-toolbar:not([data-toolbar-menu=open]) {\n        padding-left: 240px;\n        transition: none;\n      }\n\n      .gin--vertical-toolbar .toolbar-menu-administration {\n        min-width: var(--ginToolbarWidth, 240px);\n        transition: none;\n      }\n    }\n    ";
      var scriptTag = document.querySelector("script");
      scriptTag.parentNode.insertBefore(style, scriptTag);
    } else document.getElementsByClassName(className).length > 0 && document.getElementsByClassName(className)[0].remove();
  }
})();