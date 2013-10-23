'use strict';

angular.module('jsPlumb')
  .factory('jsPlumbService', ['$document', '$q', '$rootScope', function($document, $q, $rootScope) {

    var d = $q.defer();

    function onScriptLoad() {
      // Load client in the browser
      $rootScope.$apply(function() { d.resolve(window.jsPlumb); });
    }

    var scriptTag = $document[0].createElement('script');
    scriptTag.type = 'text/javascript';
    scriptTag.async = true;
    scriptTag.src = '//cdnjs.cloudflare.com/ajax/libs/jsPlumb/1.4.1/jquery.jsPlumb-1.4.1-all-min.js';
    scriptTag.onreadystatechange = function () {
      if (this.readyState == 'complete') onScriptLoad();
    };
    scriptTag.onload = onScriptLoad;

    var s = $document[0].getElementsByTagName('body')[0];
    s.appendChild(scriptTag);

    return {
      jsPlumb: function() {
        return d.promise;
      }
    };

  }]);
