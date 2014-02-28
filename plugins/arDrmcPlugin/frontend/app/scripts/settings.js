'use strict';

module.exports = {

  // Base path, e.g. "/~user/atom"
  basePath: Qubit.relativeUrlRoot,

  // Frontend path, e.g. "/~user/atom/index.php"
  frontendPath: Qubit.frontend,

  // DRMC path, e.g. "/~user/atom/index.php/drmc"
  DRMCPath: Qubit.frontend + 'drmc/',

  // Views, assets, etc...
  viewsPath: Qubit.relativeUrlRoot + '/plugins/arDrmcPlugin/frontend/app/views',
  assetsPath: Qubit.relativeUrlRoot + '/plugins/arDrmcPlugin/frontend/assets'

};
