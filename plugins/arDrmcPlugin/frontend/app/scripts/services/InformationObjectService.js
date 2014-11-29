(function () {

  'use strict';

  angular.module('drmc.services').service('InformationObjectService', function ($http, $q, SETTINGS) {

    // Create a map of level of descriptions IDs and its corresponding CSS class
    this.levels = {};
    for (var key in SETTINGS.drmc) {
      if (key.indexOf('lod_') === 0) {
        var name = key.slice(4).slice(0, -3).replace(/_/g, '-');
        this.levels[SETTINGS.drmc[key]] = name;
      }
    }

    // List of level of descriptions components
    var typeComponents = [
      'component',
      'artist-supplied-master',
      'artist-verified-proof',
      'archival-master',
      'exhibition-format',
      'documentation',
      'miscellaneous'
    ];

    // List of level of descriptions originated from TMS
    var tmsTypes = typeComponents.concat([
      'artwork-record'
    ]);

    this.isComponent = function (level_of_description) {
      var slug;
      if (angular.isNumber(level_of_description)) {
        slug = this.levels[level_of_description];
      } else if (angular.isString(level_of_description)) {
        slug = level_of_description;
      } else {
        throw 'Unexpected type';
      }
      return -1 < typeComponents.indexOf(slug);
    };

    this.hasTmsOrigin = function (level_of_description) {
      var slug;
      if (angular.isNumber(level_of_description)) {
        slug = this.levels[level_of_description];
      } else if (angular.isString(level_of_description)) {
        slug = level_of_description;
      } else {
        throw 'Unexpected type';
      }
      return -1 < tmsTypes.indexOf(slug);
    };

    this.getTree = function (id) {
      var self = this;
      var queries = [
        // Obtain tree
        $http({
          method: 'GET',
          url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/tree'
        }),
        // Obtain associations
        $http({
          method: 'GET',
          url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/tree/associations'
        })
      ];
      return $q.all(queries).then(function (responses) {
        var _t = responses[0].data;
        var _a = responses[1].data;

        // Recursive function
        function i (set) {
          if (!angular.isArray(set)) {
            set = [set];
          }
          for (var j in set) {
            // Current item
            var k = set[j];
            // Set level
            k.level = self.levels[k.level_of_description_id];
            // Add associations. There may be more than one, so we won't break the
            // loop after the first match.
            if (_a.length) {
              for (var l = 0; l < _a.length; l++) {
                var as = _a[l];
                if (as.subject.id === k.id) {
                  if (angular.isUndefined(k.associations)) {
                    k.associations = [];
                  }
                  k.associations.push(as);
                }
              }
            }
            // Recursivity
            if (angular.isDefined(k.children) && k.children !== null) {
              i(k.children);
            }
          }
        }

        i(_t);

        return _t;
      });
    };

    this.getById = function (id, params) {
      params = params || {};
      var configuration = {
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id
      };
      if (Object.keys(params).length > 0) {
        configuration.params = params;
      }
      var self = this;
      return $http(configuration).success(function (data) {
        data.level = self.levels[data.level_of_description_id];
      });
    };

    this.get = function (params) {
      params = params || {};
      var configuration = {
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects'
      };
      if (Object.keys(params).length > 0) {
        configuration.params = params;
      }
      var self = this;
      return $http(configuration).success(function (data) {
        data.level = self.levels[data.level_of_description_id];
      });
    };

    this.getSupportingTechnologyRecord = function (id) {
      var params = { level_id: SETTINGS.drmc.lod_supporting_technology_record_id };
      return this.getById(id, params);
    };

    this.getSupportingTechnologyRecords = function (params) {
      params = params || {};
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/technologies',
        params: params
      });
    };

    this.setSupportingTechnologyRecords = function (id, relationships) {
      return $http({
        method: 'POST',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/technologies',
        data: relationships
      });
    };

    this.getSupportingTechnologyRecordsOf = function (id) {
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/technologies'
      }).then(function (response) {
        return response.data;
      });
    };

    this.getFiles = function (params) {
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/files',
        params: params
      });
    };

    this.getMets = function (id) {
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/mets'
      });
    };

    this.getWorks = function (params) {
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/works',
        params: params
      });
    };

    this.getComponents = function (params) {
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/components',
        params: params
      });
    };

    this.getWork = function (id) {
      var params = { level_id: SETTINGS.drmc.lod_artwork_record_id };
      return this.getById(id, params);
    };

    this.getDigitalObject = function (id) {
      return this.getDigitalObjects(id, true);
    };

    this.getDigitalObjects = function (id, excludeDescendants, params) {
      params = params || {};
      var configuration = {
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/files',
        params: params
      };
      if (typeof excludeDescendants !== 'undefined' && excludeDescendants === true) {
        configuration.params = { excludeDescendants: true };
      }
      return $http(configuration);
    };

    /**
     * From here, successCallback is returning the contents of the response
     * instead of the response object
     */

    this.getArtworkStatus = function (id) {
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/status'
      }).then(function (response) {
        return response.data;
      });
    };

    this.getTms = function (id) {
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/tms'
      }).then(function (response) {
        return response.data;
      });
    };

    this.getArtworkRecordWithTms = function (id) {
      return $q.all([
        this.getWork(id),
        this.getTms(id)
      ]).then(function (responses) {
        var data = responses[0].data;
        data.tms = responses[1];
        return data;
      });
    };

    this.getAips = function (id) {
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/aips'
      }).then(function (response) {
        return response.data;
      });
    };

    this.create = function (data) {
      var configuration = {
        method: 'POST',
        url: SETTINGS.frontendPath + 'api/informationobjects',
        data: data
      };
      return $http(configuration).then(function (response) {
        return response.data;
      });
    };

    this.update = function (id, data) {
      var configuration = {
        method: 'PUT',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id,
        data: data
      };
      return $http(configuration).then(function (response) {
        return response.data;
      });
    };

    this.delete = function (id) {
      return $http({
        method: 'DELETE',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id
      });
    };

    this.move = function (id, parentId) {
      return $http({
        method: 'POST',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + id + '/move',
        data: {
          parent_id: parentId
        }
      });
    };

    /**
     * Supporting technology records
     */

    this.createSupportingTechnologyRecord = function (data) {
      data.level_of_description_id = SETTINGS.drmc.lod_supporting_technology_record_id;
      return this.create(data);
    };

    /**
     * Associative relationships
     */

    this.associate = function (source_id, target_id, type_id, options) {
      var data = {
        target_id: target_id,
        type_id: type_id
      };
      if (angular.isDefined(options) && angular.isDefined(options.description)) {
        data.description = options.description;
      }
      return $http({
        method: 'POST',
        url: SETTINGS.frontendPath + 'api/informationobjects/' + source_id + '/associate',
        data: data
      });
    };

    this.getAssociation = function (id) {
      return $http({
        method: 'GET',
        url: SETTINGS.frontendPath + 'api/informationobjects/association/' + id
      });
    };

    this.updateAssociation = function (id, data) {
      return $http({
        method: 'PUT',
        url: SETTINGS.frontendPath + 'api/informationobjects/association/' + id,
        data: data
      });
    };

    this.deleteAssociation = function (id) {
      return $http({
        method: 'DELETE',
        url: SETTINGS.frontendPath + 'api/informationobjects/association/' + id
      });
    };

  });

})();
