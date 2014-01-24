'use strict';

module.exports = function (ATOM_CONFIG) {
  return {
    restrict: 'E',
    scope: {
      collection: '='
    },
    templateUrl: ATOM_CONFIG.viewsPath + '/partials/context-browser.html',
    replace: true,
    link: function (scope, element) {

      // Import cbd, the context browser module
      // I can't remember what is the 'd' for :P
      // var cbd = new require('../lib/cbd');
      // console.log(cbd);
      // console.log(new require('../lib/cbd'));

      // This layer will be the closest HTML container of the SVG
      var container = element.find('.container');
      console.log(container, scope);

      // SVG objects
      // var rootSVG = d3.select(container.get(0)).append('svg');
      // var graphSVG = rootSVG.append('svg').attr('width', '100%').attr('height', '100%').attr('class', 'graph-attach');

      // var graph = cbd.utils.createDagreGraph(scope.collection);
      // console.log(graph);

      // var layout = dagreD3.layout().nodeSep(20).rankDir('RL');
      // var renderer = new dagreD3.Renderer();
      // renderer.drawNodes();
      // renderer.layout(layout);
      // renderer.run(graph, graphSVG);

    }
  };
};
