(($) => {
  "use strict";

  // Check navbar children possition in relation to the
  // navbar and indicate wrapped status with a class when
  // one of them is below a 20 pixels margin.
  function navbarWrap($navbar) {
    $navbar.children().each((_, child) => {
      if ($(child).offset().top > $navbar.offset().top + 20) {
        $navbar.addClass("wrapped");
        return false;
      }
      $navbar.removeClass("wrapped");
    });
  }

  $(() => {
    var $navbar = $("#navbar-content");

    // Check navbar wrap on document ready
    navbarWrap($navbar);

    // And on window resize
    $(window).on("resize", () => {
      navbarWrap($navbar);
    });
  });
})(jQuery);
