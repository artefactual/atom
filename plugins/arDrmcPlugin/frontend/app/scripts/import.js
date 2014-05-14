(function () {

  'use strict';

  // We make some of our vendors globally available because that's what other
  // libraries are expecting, e.g. hotkeys.js expects window.Moustrap to be
  // defined and so on...

  window.jQuery = require('jquery');
  window.angular = require('angular');

  require('d3');
  require('rickshaw');
  require('dagre');
  require('wolfy87-eventemitter');

  // These folks dont use module.exports. TODO: use browserify-shim?
  window.Mousetrap = require('../../node_modules/mousetrap/mousetrap.js');
  require('../../node_modules/angular-hotkeys/src/hotkeys.js');
  require('../../node_modules/angular-ui-router/release/angular-ui-router.js');
  require('../../vendor/angular-ui.js'); // ... and this ones not even npm

})();

