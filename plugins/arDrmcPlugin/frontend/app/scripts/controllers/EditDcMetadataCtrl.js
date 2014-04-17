'use strict';

module.exports = function ($scope, $state, $modalInstance, SETTINGS, InformationObjectService, ActorsService, TaxonomyService, id, parentId) {

  $scope.tooltips = {
    identifier: 'The unambiguous reference code used to uniquely identify this resource.',
    title: 'The name given to this resource.',
    name: 'Identify and record the name(s) and type(s) of the unit of description.',
    date: 'Identify and record the date(s) of the unit of description. Identify the type of date given. Record as a single date or a range of dates as appropriate. The Date display field can be used to display free-text date information including typographical marks to express approximation, uncertainty or qualification.',
    description: 'An abstract, table of contents or description of the resource\'s scope and contents.',
    type: 'The nature or genre of the resource. Assign as many types as applicable. The Type options are limited to the DCMI Type vocabulary. Assign the \'Collection\' value if this resource is the top-level for a set of lower-level (child) resources. Please note: if this resource is linked to a digital object, the image, text, sound or moving image types are added automatically upon output, so do not duplicate those values here.',
    format: 'The file format, physical medium or dimensions of the resource. Please note: If this resource is linked to a digital object, the internet media types (MIME) will be added automatically upon output, so don\'t duplicate those values here.',
    source: 'Related material(s) from which this resource is derived.',
    conditions: 'Information about rights held in and over the resource (e.g. copyright, access conditions, etc.).'
  };

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.modalContainer = {};

  // New record?
  $scope.new = id === null;

  /**
   * TODO: the following async calls should probably being resolved before the
   * controller is initialized (from the service), otherwise we are going to
   * have a bunch of unnecessary loops for request.
   */

  var init = function () {
    // Parent
    if (parentId !== null) {
      $scope.resource.parent_id = parentId;
      if ($scope.new) {
        InformationObjectService.getById(parentId).then(function (response) {
          $scope.resource.parent = response.data.title;
        });
      }
    }
    // Necessary hack until we have the directives implemented for multivalues
    if (!$scope.resource.hasOwnProperty('types')) {
      $scope.resource.types = [];
    }
    if (!$scope.resource.hasOwnProperty('dates')) {
      $scope.resource.dates = [];
    }
    if (!$scope.resource.hasOwnProperty('names')) {
      $scope.resource.names = [];
    }
  };

  // Initialize
  var backup;
  if ($scope.new) {
    $scope.resource = {};
    init();
    backup = $scope.resource;
  } else {
    InformationObjectService.getById(id).then(function (response) {
      $scope.resource = response.data;
      init();
      backup = angular.copy($scope.resource);
    });
  }

  // Populate required taxonomies
  TaxonomyService.getTerms('EVENT_TYPE').then(function (data) {
    $scope.eventTypesTaxonomy = data.terms;
  });
  TaxonomyService.getTerms('DC_TYPES').then(function (data) {
    $scope.dcTypesTaxonomy = data.terms;
  });

  // Title, based in new
  if ($scope.new) {
    $scope.title = 'Add supporting technology record';
  } else {
    $scope.title = 'Edit supporting technology record';
  }

  // Update existing record
  var update = function () {
    InformationObjectService.update($scope.resource.id, $scope.resource).then(function () {
      $modalInstance.close();
    }, function () {
      $modalInstance.dismiss('Dublin Core metadata could not be saved');
    });
  };

  // Create new record
  var create = function () {
    InformationObjectService.createSupportingTechnologyRecord($scope.resource).then(function (data) {
      if (parentId === null) {
        $state.go('main.technology-records.view', { id: data.id });
      }
      $modalInstance.close();
    }, function () {
      $modalInstance.dismiss('Dublin Core metadata could not be created');
    });
  };

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  // Form submission callback
  $scope.submit = function () {
    if ($scope.modalContainer.form.$invalid) {
      return;
    }
    if ($scope.new) {
      create();
    } else {
      update();
    }
  };

  $scope.reset = function ($event) {
    // I don't understand what's the default? Submit even if type not defined?
    $event.preventDefault();
    $scope.resource = angular.copy(backup);
  };

  // Fetcher for the typeahead
  $scope.searchActor = function (viewValue) {
    return ActorsService.getActors(viewValue).then(function (data) {
      // Remove current selection
      delete $scope.resource.names[0].actor_id;
      // Collect matches for typeahead
      var matches = [];
      angular.forEach(data.results, function (value, key) {
        matches.push({
          id: key,
          authorized_form_of_name: value.authorized_form_of_name
        });
      });
      return matches;
    });
  };

  $scope.onSelectActor = function ($item) {
    $scope.resource.names[0].actor_id = $item.id;
    $scope.resource.names[0].authorized_form_of_name = $item.authorized_form_of_name;
  };
};
