(function (root) {
  "use strict";

  var modernizr = root.Modernizr || {};
  var input = modernizr.input || {};

  input.placeholder = "placeholder" in document.createElement("input");
  modernizr.input = input;
  root.Modernizr = modernizr;
})(window);
