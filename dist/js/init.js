(() => {
  function ginInitDarkmode() {
    1 == localStorage.getItem("Backdrop.gin.darkmode") || "auto" === localStorage.getItem("Backdrop.gin.darkmode") && window.matchMedia("(prefers-color-scheme: dark)").matches ? document.documentElement.classList.add("gin--dark-mode") : !0 === document.documentElement.classList.contains("gin--dark-mode") && document.documentElement.classList.remove("gin--dark-mode");
  }
  if (localStorage.getItem("GinDarkMode") && (localStorage.setItem("Backdrop.gin.darkmode", localStorage.getItem("GinDarkMode")), 
  localStorage.removeItem("GinDarkMode")), localStorage.getItem("GinSidebarOpen") && (localStorage.setItem("Backdrop.gin.toolbarExpanded", localStorage.getItem("GinSidebarOpen")), 
  localStorage.removeItem("GinSidebarOpen")), ginInitDarkmode(), window.addEventListener("DOMContentLoaded", (() => {
    localStorage.getItem("Backdrop.gin.darkmode") && Backdrop.settings.gin.darkmode == localStorage.getItem("Backdrop.gin.darkmode") || (localStorage.setItem("Backdrop.gin.darkmode", Backdrop.settings.gin.darkmode), 
    ginInitDarkmode());
  })), localStorage.getItem("Backdrop.gin.sidebarWidth")) {
    const sidebarWidth = localStorage.getItem("Backdrop.gin.sidebarWidth");
    document.documentElement.style.setProperty("--gin-sidebar-width", sidebarWidth);
  }
  if (localStorage.getItem("Backdrop.gin.sidebarExpanded.desktop")) {
    const style = document.createElement("style"), className = "gin-sidebar-inline-styles";
    if (style.className = className, window.innerWidth < 1024 || "false" === localStorage.getItem("Backdrop.gin.sidebarExpanded.desktop")) {
      style.innerHTML = "\n    body {\n      --gin-sidebar-offset: 0px;\n      padding-inline-end: 0;\n      transition: none;\n    }\n\n    .layout-region-node-secondary {\n      transform: translateX(var(--gin-sidebar-width, 360px));\n      transition: none;\n    }\n\n    .meta-sidebar__overlay {\n      display: none;\n    }\n    ";
      const scriptTag = document.querySelector("script");
      scriptTag.parentNode.insertBefore(style, scriptTag);
    } else document.getElementsByClassName(className).length > 0 && document.getElementsByClassName(className)[0].remove();
  }
})();