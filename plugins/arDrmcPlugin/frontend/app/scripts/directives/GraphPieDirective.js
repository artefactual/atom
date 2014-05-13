'use strict';

var arD3 = require('d3');

module.exports = function ($filter) {
  return {
    restrict: 'E',
    replace: true,
    scope: {
    },
    template: '<div><rs-y-axis></rs-y-axis><rs-chart></rs-chart><rs-x-axis></rs-x-axis><rs-legend></rs-legend></div>',
    link: function (scope, element, attrs) {

      // Attributes for this directive
      // w = width and height of pie graph
      // access-key: variable core data key from API
      // format-key: variable format key from API

      attrs.$observe('data', function (graphSpecification) {

        if (graphSpecification && attrs.width) {

          graphSpecification = JSON.parse(graphSpecification);

          var dataset = graphSpecification.data,
              w = attrs.width,
              h = attrs.width,
              color = arD3.scale.category20();

          angular.forEach(dataset, function (obj, key) {
            dataset[key].accessKey = dataset[key][graphSpecification.accessKey];
            dataset[key].formatKey = dataset[key][graphSpecification.formatKey];
            delete dataset[key][graphSpecification.accessKey];
            delete dataset[key][graphSpecification.formatKey];
          });

          var outerRadius = w / 2;
          var innerRadius = 0;
          var arc = arD3.svg.arc()
            .innerRadius(innerRadius)
            .outerRadius(outerRadius);

          var pie = arD3.layout.pie()
            .value(function (d) {
              return d.accessKey;
            });

          // Create SVG element
          var svg = arD3.select(element[0])
            .append('svg')
            .attr('width', w)
            .attr('height', h);

          // Set up groups
          var arcs = svg.selectAll('g.arc')
            .data(pie(dataset))
            .enter()
            .append('g')
            .attr('class', 'arc')
            .attr('transform', 'translate(' + outerRadius + ',' + outerRadius + ')');

          // Draw arc paths
          arcs.append('path')
            .attr('fill', function (d, i) {
              return color(i);
            })
            .attr('d', arc);

          //Labels
          /*arcs.append('svg:text')
            .attr('transform', function (d) {
              return 'translate(' + arc.centroid(d) + ')';
            })
            .attr('text-anchor', 'middle')
            .text(function (d) {
              return d.data.media_type;
            });*/

          // Legend
          var label_width = Number.MIN_VALUE;

          for (var i = 0; i < dataset.length; i++) {
            label_width = Math.max(label_width, dataset[i].formatKey.length);
          }
          // round up to 10th
          label_width = label_width * 13;
          var label_height = dataset.length * 20;

          var legend = arD3.select(element[0]).append('svg')
            .attr('class', 'legend')
            .attr('width', label_width)
            .attr('height', label_height)
            .selectAll('g')
            .data(color.domain().slice().reverse())
            .enter().append('g')
            .attr('transform', function (d, i) { return 'translate(0,' + i * 20 + ')'; });

          legend.append('rect')
            .attr('width', 18)
            .attr('height', 18)
            .style('fill', color);

          // Add digits
          legend.append('text')
            .attr('x', 24)
            .attr('y', 14)
            .text(function (d) {
              var value;

              if (attrs.unitFilter === 'on') {
                value = $filter('UnitFilter')(dataset[d].accessKey, 2, true);
                return value;
              } else {
                value = dataset[d].accessKey;
                return value;
              }
            });

          // Add text
          legend.append('text')
          .attr('x', 94)
          .attr('y', 14)
          .text(function (d) {
            var label = dataset[d].formatKey;
            return label;
          });
        }
      });
    }
  };
};
