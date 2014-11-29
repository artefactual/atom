(function () {

  'use strict';

  angular.module('drmc').constant('SETTINGS', {

    // Base path, e.g. "/~user/atom"
    basePath: Qubit.relativeUrlRoot,

    // Frontend path, e.g. "/~user/atom/index.php"
    frontendPath: Qubit.frontend,

    // DRMC path, e.g. "/~user/atom/index.php/drmc"
    DRMCPath: Qubit.frontend + 'drmc/',

    // Things like levels of description, etc...
    drmc: Qubit.drmc,

    // Views, assets, etc...
    viewsPath: Qubit.relativeUrlRoot + '/plugins/arDrmcPlugin/frontend/app/views',
    assetsPath: Qubit.relativeUrlRoot + '/plugins/arDrmcPlugin/frontend/assets'

  });

})();
