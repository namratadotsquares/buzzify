
// (function (cash) {

//   "use strict";

//   if (localStorage.getItem("admin-theme-mode") == 'light') {

//     $("html").removeClass('dark');

//     $(".dark-mode-switcher__toggle").removeClass('dark-mode-switcher__toggle--active');

//   }

//   else if (localStorage.getItem("admin-theme-mode") == 'dark') {

//     $("html").addClass('dark');

//     $(".dark-mode-switcher__toggle").addClass('dark-mode-switcher__toggle--active');

//   }

//   else if (localStorage.getItem("admin-theme-mode") == undefined) {

//     $("html").removeClass('dark');

//     $(".dark-mode-switcher__toggle").removeClass('dark-mode-switcher__toggle--active');

//   }

//   // Copy original code

//   cash('.dark-mode-switcher').on('click', function () {

//     let switcher = cash(this).find('.dark-mode-switcher__toggle')

//     if (cash(switcher).hasClass('dark-mode-switcher__toggle--active')) {

//       cash(switcher).removeClass('dark-mode-switcher__toggle--active')

//     } else {

//       cash(switcher).addClass('dark-mode-switcher__toggle--active')

//     }

//     if (localStorage.getItem("admin-theme-mode") == 'light') {

//       localStorage.setItem("admin-theme-mode", "dark");

//       $("html").addClass('dark');

//       // $(".mode_icon").addClass('fa-sun-o');

//       // $(".mode_icon").removeClass('fa-moon-o');

//     }

//     else if (localStorage.getItem("admin-theme-mode") == 'dark') {

//       localStorage.setItem("admin-theme-mode", "light");

//       $("html").removeClass('dark');

//     }

//     else if (localStorage.getItem("admin-theme-mode") == undefined) {

//       localStorage.setItem("admin-theme-mode", "dark");

//       $("html").addClass('dark');

//     }

//   })

// })(cash)

(function () {
  "use strict";

  // Dark mode switcher
  $(".dark-mode-switcher").on("click", function () {
console.log("Dark mode switcher in resourse file");
    let switcher = $(this).find(".dark-mode-switcher__toggle");
    if ($(switcher).hasClass("dark-mode-switcher__toggle--active")) {
      $(switcher).removeClass("dark-mode-switcher__toggle--active");
    } else {
      $(switcher).addClass("dark-mode-switcher__toggle--active");
    }

    setTimeout(() => {
      let link = $(".dark-mode-switcher").data("url");
      window.location.href = link;
    }, 500);
  });
})();
