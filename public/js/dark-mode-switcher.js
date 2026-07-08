(function(cash) { 
    "use strict";
  console.log(localStorage.getItem("admin-theme-mode"));
    if(localStorage.getItem("admin-theme-mode")=='light'){

        $("html").removeClass('dark');
        $(".dark-mode-switcher__toggle").removeClass('dark-mode-switcher__toggle--active');
    }
    else if(localStorage.getItem("admin-theme-mode")=='dark'){

        $("html").addClass('dark');
        $(".dark-mode-switcher__toggle").addClass('dark-mode-switcher__toggle--active');
    }
    else if(localStorage.getItem("admin-theme-mode")==undefined){
        $("html").removeClass('dark');
        $(".dark-mode-switcher__toggle").removeClass('dark-mode-switcher__toggle--active');
    }
    // Copy original code
    cash('.dark-mode-switcher').on('click', function() {
console.log("Dark Mode Js");
      let switcher = cash(this).find('.dark-mode-switcher__toggle')
      if (cash(switcher).hasClass('dark-mode-switcher__toggle--active')) {
        cash(switcher).removeClass('dark-mode-switcher__toggle--active')
      } else {
        cash(switcher).addClass('dark-mode-switcher__toggle--active')
      }
      if(localStorage.getItem("admin-theme-mode")=='light'){

        localStorage.setItem("admin-theme-mode", "dark");
            $("html").addClass('dark');
            var url = window.location.href;
            url = url.replace(/light$/, 'dark');
            $.get(url, function(data) {
                $("#content").html(data);
                window.history.pushState(null, null, url);
            });
      }
      else if(localStorage.getItem("admin-theme-mode")=='dark'){

        localStorage.setItem("admin-theme-mode", "light");
            $("html").removeClass('dark');
            var url = window.location.href;
            url = url.replace(/dark$/, 'light');
            $.get(url, function(data) {
                $("#content").html(data);
                window.history.pushState(null, null, url);
            });
      }
      else if(localStorage.getItem("admin-theme-mode")==undefined){

        localStorage.setItem("admin-theme-mode", "light");
            $("html").addClass('light');
            var url = window.location.href;
            url = url.replace(/dark$/, 'light');
            $.get(url, function(data) {
                $("#content").html(data);
                window.history.pushState(null, null, url);
            });
      }
    })
  })(cash)