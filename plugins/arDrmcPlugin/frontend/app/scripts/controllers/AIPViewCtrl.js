'use strict';

module.exports = function ($scope, $modal, SETTINGS, $stateParams, AIPService, InformationObjectService, ModalDigitalObjectViewerService, ModalDownloadService,
  ModalReclassifyAipService, FixityService) {

  AIPService.getAIP($stateParams.uuid)
    .success(function (data) {
      $scope.aip = data;
      pullFiles();
    });

  FixityService.getAipFixity($stateParams.uuid).then(function (response) {
      var lastRecoveryAddedToResults = false,
          recoveryTimeStarted,
          recoveryTimeCompleted,
          recoveryTimeStartedDate,
          reportTimeStartedDate;

      // If recovery state/end time exist, make them compatible, for display, with report times
      if (typeof response.data.last_recovery.time_completed !== 'undefined') {
        recoveryTimeStarted = response.data.last_recovery.time_completed.replace(' ', 'T');
      }

      if (typeof response.data.last_recovery.time_completed !== 'undefined') {
        recoveryTimeCompleted = response.data.last_recovery.time_completed.replace(' ', 'T');
      }

      // Logic to append recovery data to list of fixity reports
      var appendRecoveryData = function (fixityReports) {
        fixityReports.push({
          'success': response.data.last_recovery.success,
          'recovery_message': response.data.last_recovery.message,
          'time_started': recoveryTimeStarted,
          'time_completed': recoveryTimeCompleted
        });
      };

      $scope.fixityReports = [];
      $scope.fixityFailsCount = 0;
      $scope.recoveryPending = response.data.last_recovery.pending;

      angular.forEach(response.data.results, function (v) {
        if (angular.isDefined(v.success) && v.success === false && v.recovery_needed === true) {
          $scope.fixityFailsCount = $scope.fixityFailsCount + 1;
        }

        // Add the recovery data to the list of fixiy reports, if the last recovery
        // hasn't already been shown and is was started before the current report
        recoveryTimeStartedDate = new Date(recoveryTimeStarted);
        reportTimeStartedDate = new Date(v.time_started);

        if (
          !lastRecoveryAddedToResults &&
          (recoveryTimeStartedDate.getTime() > reportTimeStartedDate.getTime())
        ) {
          appendRecoveryData($scope.fixityReports);
          lastRecoveryAddedToResults = true;
        }

        $scope.fixityReports.push(v);
      });

      // If last recovery data exists and hasn't been added, add it
      if (!lastRecoveryAddedToResults && typeof response.data.last_recovery.success !== 'undefined') {
        appendRecoveryData($scope.fixityReports);
      }
    });

  // Levels of description to determine part_of link
  $scope.artworkId = parseInt(SETTINGS.drmc.lod_artwork_record_id);
  $scope.techId = parseInt(SETTINGS.drmc.lod_supporting_technology_record_id);

  $scope.openReclassifyModal = function () {
    ModalReclassifyAipService.open($scope.aip.uuid, $scope.aip.part_of.title).result.then(function (data) {
      $scope.aip.type.id = data.type_id;
      $scope.aip.type.name = data.type;
    });
  };


  /**
   * Interaction with modals
   */

  $scope.downloadFile = function (file) {
    ModalDownloadService.downloadFile(
      $scope.aip.name,
      $scope.aip.uuid,
      file.id,
      file.original_relative_path_within_aip
    );
  };

  $scope.downloadAip = function () {
    ModalDownloadService.downloadAip($scope.aip.name, $scope.aip.uuid);
  };

  $scope.openViewer = function (files, index) {
    ModalDigitalObjectViewerService.open(files, index);
  };


  /**
   * File list widget
   */

  $scope.criteria = {};
  $scope.criteria.limit = 10;
  $scope.criteria.sort = 'name';
  $scope.page = 1;
  $scope.files = [];

  var pullFiles = function () {
    AIPService.getFiles($scope.aip.uuid, $scope.criteria)
      .success(function (data) {
        $scope.files = data.results;
        $scope.$broadcast('pull.success', data.total);
      });
  };

  // Watch for criteria changes
  $scope.$watch('criteria', function (newValue, oldValue) {
    // Reset page
    if (angular.isDefined(oldValue) && newValue.skip === oldValue.skip) {
      $scope.page = 1;
      newValue.skip = 0;
    }
    if (!$scope.files.length) {
      return;
    }
    pullFiles();
  }, true);

  // Changes in scope.page updates criteria.skip
  $scope.$watch('page', function (value) {
    $scope.criteria.skip = (value - 1) * $scope.criteria.limit;
  });

};
