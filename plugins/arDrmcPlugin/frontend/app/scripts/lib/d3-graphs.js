'use strict';

var data = [
  { 'label': 'image/gif', 'value': 90 },
  { 'label': 'image/jpeg', 'value': 55 },
  { 'label': 'video/mov', 'value': 55 },
  { 'label': 'text/pdf', 'value': 70 },
  { 'label': 'audio/mp3', 'value': 80 },
  { 'label': 'audio/flac', 'value': 45 }
];

function mimeGraph(data) {

  // Variables for colour range, width and heigh of chart
  var w = 240,
      h = 240,
      r = (h*0.5),  // for radius positioning
      colour = d3.scale.category20c(); // d3 has built-in range of colours

  var vis = d3.select('#svg_donut')
    .append('svg:svg')
    .data([data])
    .attr('width', w)
    .attr('height', h)
    .append('svg:g')
    .attr('transform', 'translate(' + r + ',' + r + ')');

  var arc = d3.svg.arc()
    .innerRadius(21)
    .outerRadius(90);

  // Gets each value from array of data
  var pie = d3.layout.pie()
    // This will create arc data for us given a list of values
    .value(function(d){ return d.value; });

  var arcs = vis.selectAll('g.slice')
    .data(pie)
    .enter()
    .append('svg:g')
    .attr('class', 'slice');

  arcs.append('svg:path')
    .attr('fill', function(d, i) {
      return colour(i);
    })
    .attr('d', arc);

  //Adding text
  arcs.append('svg:text')
    .attr('transform', function(d) {

      // Puts the label in the right place
      d.innerRadius = r;
      d.outerRadius = r;

      return 'translate(' + arc.centroid(d) + ')';
    })
    .attr('text-anchor', 'middle')
    .text(function(d, i) {
      return data[i].label;
    });
}
