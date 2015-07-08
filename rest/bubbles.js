
/* global d3, self */

function appendSVGtoDIV(div, diameter) {
    var svg = div.append("svg")
            .attr("width", diameter)
            .attr("height", diameter)
            .attr("class", "bubble");
    return svg;
}

function appendG(node) {
    var g = node.append("g")
            .attr("class", "node")
            .attr("transform", function (d) {
                return "translate(" + d.x + "," + d.y + ")";
            });
    return g;
}

function appendCircle(node) {
    return node.append("circle")
            .attr("r", function (d) {
                return d.r;
            })
            .style("fill", function (d) {
                return d.originData.color;
                //return color(d.packageName); 
            });
}

function appendTitle(node, format) {
    return node.append("title")
            .text(function (d) {
                return d.className + ": " + format(d.value);
            });
}

function appendA(node) {
    return node.append("a")
            .attr("xlink:href", function (d) {
                return d.originData.moodleURL;
            })
            .attr("data-toggle", "popover")
            .attr("title", "popovertitle")
            .attr("data-content", "content");
}

function appendImage(node, imageScalingFactor) {
    return node.append("image")
            .attr("xlink:href", function (d) {
                return d.originData.iconURL;
            })
            .attr("x", function (d) {
                return -0.5 * imageScalingFactor * d.r;
            })
            .attr("y", function (d) {
                return -0.5 * imageScalingFactor * d.r;
            })
            .attr("height", function (d) {
                return d.r * imageScalingFactor;
            })
            .attr("width", function (d) {
                return d.r * imageScalingFactor;
            });
}

function run(json) {
    console.log(json);
    // Getting max width
    var divs = $(".activity");
    var width = divs[0].offsetWidth * 0.6; // - paddingLeft;

    var diameter = width,
            format = d3.format(",d"),
            color = d3.scale.category20c();

    var bubble = d3.layout.pack()
            .sort(null)
            .size([diameter, diameter])
            .padding(1.5);

   
    var svg = appendSVGtoDIV(d3.select("#learninganalytics_div"), diameter);


//d3.json(json, 
    var process = function (root) {
        var node = svg.selectAll(".node")
                .data(bubble.nodes(classes(root))
                        .filter(function (d) {
                            return !d.children;
                        }))
                .enter();

        node = appendA(node);

        var g = appendG(node);

        appendTitle(g, format);

        appendCircle(g);

        var imageScalingFactor = 0.8;

        appendImage(g, imageScalingFactor);


    };
    process(json);
// Returns a flattened hierarchy containing all leaf nodes under the root.


    d3.select(self.frameElement).style("height", diameter + "px");
    $('[data-toggle="popover]"').each(
            $(this).popover({
        title: "Hi!",
        content: "popover",
        container: $(this),
        placement: "top"
    })
            );
}

function classes(root) {
    var classes = [];

    function recurse(name, node) {
        if (node.children)
            node.children.forEach(function (child) {
                recurse(node.name, child);
            });
        else
            classes.push({packageName: name, className: node.name, value: node.size, originData: node});
    }

    recurse(null, root);
    return {children: classes};
}
