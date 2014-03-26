'use strict';

module.exports = function ($scope, InformationObjectService, $modalInstance, ActorsService, resource) {

  $scope.tooltips = {
    identifier: 'The unambiguous reference code used to uniquely identify this resource.',
    title: 'The name given to this resource.',
    name: 'Identify and record the name(s) and type(s) of the unit of description.',
    date: 'Record as a single date or a range of dates as appropriate. Use the start and end fields to make the dates searchable. Do not use any qualifiers or typographical symbols to express uncertainty. Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM, YYYY.',
    dateStart: ' Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM, YYYY.',
    dateEnd: ' Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM, YYYY.',
    description: 'An abstract, table of contents or description of the resource\'s scope and contents.',
    type: 'The nature or genre of the resource. Assign as many types as applicable. The Type options are limited to the DCMI Type vocabulary. Assign the \'Collection\' value if this resource is the top-level for a set of lower-level (child) resources. Please note: if this resource is linked to a digital object, the image, text, sound or moving image types are added automatically upon output, so do not duplicate those values here.',
    format: 'The file format, physical medium or dimensions of the resource. Please note: If this resource is linked to a digital object, the internet media types (MIME) will be added automatically upon output, so don\'t duplicate those values here.',
    source: 'Related material(s) from which this resource is derived.',
    conditions: 'Information about rights held in and over the resource (e.g. copyright, access conditions, etc.).'
  };

  // New record?
  $scope.new = true;

  // Edit mode, when we receive a resoruce
  if (resource !== false) {
    $scope.resource = resource;
    $scope.new = false;
  }

  // Title, based in new
  if ($scope.new) {
    $scope.title = 'Add supporting technology record';
  } else {
    $scope.title = 'Edit supporting technology record';
  }

  // HACK: form scoping issue within modals, see
  // - http://stackoverflow.com/a/19931221/2628967
  // - https://github.com/angular-ui/bootstrap/issues/969
  $scope.form = {};

  // Save changes
  $scope.save = function () {
    InformationObjectService.update(resource.id, $scope.form.dc).then(function () {
      $modalInstance.close();
    }, function () {
      $modalInstance.dismiss('Dublin Core metadata could not be saved');
    });
  };

  $scope.create = function () {
    InformationObjectService.create($scope.form.dc).then(function () {
      $modalInstance.close();
    }, function () {
      $modalInstance.dismiss('Dublin Core metadata could not be create');
    });
  };

  // Close the dialog
  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  // Fetcher for the typeahead
  $scope.searchActors = function (viewVal) {
    return ActorsService.getActors(viewVal);
  };

  // *** TYPES ***
  //To fix: make dropdown
  $scope.dcTypes = [
    'collection',
    'dataset',
    'event',
    'image',
    'interactive resource',
    'moving image',
    'physical object',
    'service',
    'software',
    'sound',
    'still image',
    'text'
  ];

  $scope.selectedTypes = [];

  // Push selected type into new array (selectedTypes[])
  $scope.pushType = function (type) {
    var t = $scope.dcTypes.indexOf(type.toString());
    var selectedType = $scope.dcTypes.splice(t, 1).pop();
    $scope.selectedTypes.push(selectedType);
  };

  // Push selected type into unselected array (dcTypes[])
  $scope.reversePushType = function (selected) {
    var r = $scope.selectedTypes.indexOf(selected.toString());
    var revertType = $scope.selectedTypes.splice(r, 1).pop();
    $scope.dcTypes.push(revertType);
  };
};
