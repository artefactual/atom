data = [
        {"label": "image/gif", "value": 90},
        {"label":"image/jpeg", "value":55},
        {"label":"video/mov", "value":55},
        {"label":"text/pdf", "value":70},
        {"label":"audio/mp3", "value":80},
        {"label":"audio/flac", "value":45}
        ];

/* ----------------------
MIME Type Graph ------- */
function mimeGraph(data){
    //variables for colour range, width and heigh of chart

    var w = 240,
    h = 240,
    //for radius positioning
    r = (h*.5),
    colour = d3.scale.category20c(); //d3 has built-in range of colours


    var vis = d3.select('#svg_donut')
    .append('svg:svg')
    .data([data])
    .attr('width', w)
    .attr('height', h)
    .append('svg:g')
    .attr("transform", "translate(" + r + "," + r + ")");


    var arc = d3.svg.arc()
    .innerRadius(21)
    .outerRadius(90);

    //gets each value from array of data
    var pie = d3.layout.pie()

    //this will create arc data for us given a list of values
    .value(function(d){ return d.value; });

    var arcs = vis.selectAll('g.slice')
    .data(pie)
    .enter()
    .append("svg:g")
    .attr('class', 'slice');

    arcs.append('svg:path')
    .attr('fill', function(d, i){ return colour(i); })
    .attr('d', arc);

    //adding text
    arcs.append('svg:text')
    .attr('transform', function(d){
        //puts the label in the right place
        d.innerRadius = r;
        d.outerRadius = r;
        return 'translate(' + arc.centroid(d) + ')'; //built-in
        })
    .attr('text-anchor', 'middle')
    .text(function(d, i){ return data[i].label; });

}

// mimeGraph(data);











