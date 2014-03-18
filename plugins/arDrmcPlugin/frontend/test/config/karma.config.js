module.exports = function(config) {
  config.set({

    basePath: '../..',

    frameworks: ['jasmine'],

    files: [
      '../../../vendor/jquery.js',
      '../../../plugins/sfDrupalPlugin/vendor/drupal/misc/drupal.js',
      '../../../vendor/yui/yahoo-dom-event/yahoo-dom-event.js',
      '../../../vendor/yui/element/element-min.js',
      '../../../vendor/yui/button/button-min.js',
      '../../../vendor/yui/container/container_core-min.js',
      '../../../vendor/yui/menu/menu-min.js',
      '../../../vendor/modernizr.js',
      '../../../vendor/jquery-ui.js',
      '../../../vendor/jquery.expander.js',
      '../../../vendor/jquery.masonry.js',
      '../../../vendor/jquery.imagesloaded.js',
      '../../../vendor/bootstrap/js/bootstrap.js',
      '../../../vendor/URI.js',
      '../../../js/qubit.js',
      '../../../js/treeView.js',
      '../../../plugins/arDrmcPlugin/frontend/dist/DRMC.js',
      '../../../js/dominion.js',
      'dist/DRMC.js',
      'test/unit/**/*.spec.js'
    ],

    reporters: ['progress'],

    port: 9876,

    colors: true,

    logLevel: config.LOG_INFO,

    browsers: ['PhantomJS'],

    captureTimeout: 5000,

    singleRun: true,

    reportSlowerThan: 500,

    plugins: [
      'karma-jasmine',
      'karma-phantomjs-launcher'
    ]

  });
};
