data = [
        {"label":"image/tiff", "value":40},
        {"label":"image/png", "value":105},
        {"label": "image/gif", "value": 90},
        {"label":"image/jpeg", "value":45},
        {"label":"video/mov", "value":45},
        {"label":"3gpp2", "value":55},
        {"label":"text/pdf", "value":90},
        {"label":"audio/mp3", "value":80},
        {"label":"audio/flac", "value":45}
        ];

/* ----------------------
MIME Type Graph ------- */
function mimeGraph(data){
    //variables for colur range, width and heigh of chart

    var w = 200,
    h = 200,
    //for radius positioning
    r = (h*.5),
    colour = d3.scale.category20c(); //d3 has built-in range of colours


    var vis = d3.select('#AIP-mime-type')
    .append('svg:svg')
    .data([data])
    .attr('width', w)
    .attr('height', h)
    .append('svg:g')
    .attr("transform", "translate(" + r + "," + r + ")")

    var arc = d3.svg.arc()
    .innerRadius(95)
    .outerRadius(200);

    //gets each value from array of data
    var pie = d3.layout.pie()
    .value(function(d){ return d.value; });           //this will create arc data for us given a list of values

    var arcs = vis.selectAll('g.slice')
    .data(pie)
    .enter()
    .append("svg:g")
    .attr('class', 'slice');

    arcs.append('svg:path')
    .attr('fill', function(d, i){ return colour(i); })
    .attr('d', arc);

    arcs.append('svg:text')
    .attr('transform', function(d){
        //puts the label in the right place
        d.innerRadius = 0;
        d.outerRadius = r;
        return 'translate(' + arc.centroid(d) + ')'; //built-in
        })
    .attr('text-anchor', 'middle')

    .text(function(d, i){ return data[i].label; });
}

mimeGraph(data);

/* ----------------------
Woroks by Type Graph (sidebar) ------- */
// worksByDeptData = [
//         {"label":"Architecture & Design", "value":300},
//         {"label":"Drawings", "value":150},
//         {"label": "Film", "value": 110},
//         {"label":"Media & Performance Art", "value":105},
//         {"label":"Painting & Sculpture", "value":90},
//         {"label":"Photography", "value":15},
//         {"label":"Prints & Illustrated Books", "value":125}
//         ];

// function worksByDeptGraph(data){
//     //variables for colur range, width and heigh of chart

//     var w = 600,
//     h = 400,
//     //for radius positioning
//     r = (h*.5),
//     colour = d3.scale.category20c(); //d3 has built-in range of colours


//     var vis = d3.select('#svg_donut')
//     .append('svg:svg')
//     .data([worksByDeptData])
//     .attr('width', w)
//     .attr('height', h)
//     .append('svg:g')
//     .attr("transform", "translate(" + r + "," + r + ")")

//     var arc = d3.svg.arc()
//     // .innerRadius(95)
//     .outerRadius(200);

//     //gets each value from array of data
//     var pie = d3.layout.pie()
//     .value(function(d){ return d.value; });           //this will create arc data for us given a list of values

//     var arcs = vis.selectAll('g.slice')
//     .data(pie)
//     .enter()
//     .append("svg:g")
//     .attr('class', 'slice');

//     arcs.append('svg:path')
//     .attr('fill', function(d, i){ return colour(i); })
//     .attr('d', arc);

//     arcs.append('svg:text')
//     .attr('transform', function(d){
//         //puts the label in the right place
//         d.innerRadius = 100;
//         d.outerRadius = r;
//         return 'translate(' + arc.centroid(d) + ')'; //built-in
//         })
//     .attr('text-anchor', 'middle')
//     .attr('class', 'worksByDeptText')
//     .text(function(d, i){ return data[i].label; });

// }

// worksByDeptGraph(data);











