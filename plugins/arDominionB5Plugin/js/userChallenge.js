/**
 * File: userChallenge.js
 * Description: Handles the countdown, cookie setting, and redirection for the user challenge.
 */
(($) => {
  "use strict";

  $(() => {
    var $challenge = $("#user-challenge");
    if (!$challenge.length) {
      // If we're not on the challenge page, exit early.
      return;
    }

    // Read the configuration from data attributes.
    var delaySeconds = parseInt($challenge.data("delay-seconds"), 10) || 5;
    var cookieNameHasJs = $challenge.data("cookie-name-has-js");
    var cookieValue = $challenge.data("cookie-value");
    var cookieNameHeadless = $challenge.data("cookie-name-headless");
    var requestedUri = $challenge.data("requested-uri") || "/";

    // Find the countdown display element.
    var $countdown = $challenge.find("#countdown");
    var secondsLeft = delaySeconds;
    $countdown.text(secondsLeft);

    // Update the countdown every second.
    var timer = setInterval(function () {
      secondsLeft--;
      if (secondsLeft <= 0) {
        clearInterval(timer);
        // Set cookies with an expiration of 30 seconds.
        setCookie(cookieNameHasJs, cookieValue, 30);
        var headlessValue = cookieValue + ":" + navigator.webdriver;
        setCookie(
          cookieNameHeadless,
          encodeURIComponent(btoa(headlessValue)),
          30
        );

        // Redirect to the originally requested URL.
        window.location.href = requestedUri;
      } else {
        $countdown.text(secondsLeft);
      }
    }, 1000);

    /**
     * Helper function to set a cookie.
     * @param {string} name - Cookie name.
     * @param {string} value - Cookie value.
     * @param {number} seconds - Lifetime in seconds.
     */
    function setCookie(name, value, seconds) {
      var date = new Date();
      date.setTime(date.getTime() + seconds * 1000);
      var expires = "expires=" + date.toUTCString();
      document.cookie =
        name + "=" + value + "; " + expires + "; path=/; samesite=strict";
    }
  });
})(jQuery);
