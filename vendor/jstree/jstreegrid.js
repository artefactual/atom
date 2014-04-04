/*
 * jsTreeGrid 3.0.0-beta4
 * http://github.com/deitch/jstree-grid
 *
 * This plugin handles adding a grid to a tree to display additional data
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 * 
 * Works only with jstree "v3.0.0-beta5" and higher
 *
 * $Date: 2014-02-07 $
 * $Revision:  3.0.0-beta2 $
 */

/*jslint nomen:true */
/*global window,navigator, document, jQuery*/

(function ($) {
	var renderAWidth, renderATitle, getIndent, htmlstripre,
	SPECIAL_TITLE = "_DATA_", LEVELINDENT = 24, bound = false, styled = false;
	
	/*jslint regexp:true */
	htmlstripre = /<\/?[^>]+>/gi;
	/*jslint regexp:false */
	
	getIndent = function(node,tree) {
		var div, i, li, width;
		
		// did we already save it for this tree?
		tree._gridSettings = tree._gridSettings || {};
		if (tree._gridSettings.indent > 0) {
			width = tree._gridSettings.indent;
		} else {
			// create a new div on the DOM but not visible on the page
			div = $("<div></div>");
			i = node.prev("i");
			li = i.parent();
			// add to that div all of the classes on the tree root
			div.addClass(tree.get_node("#",true).attr("class"));
		
			// move the li to the temporary div root
			li.appendTo(div);
			
			// attach to the body quickly
			div.appendTo($("body"));
		
			// get the width
			width = i.width() || LEVELINDENT;
		
			// detach the li from the new div and destroy the new div
			li.detach();
			div.remove();
			
			// save it for the future
			tree._gridSettings.indent = width;
		}
		
		
		return(width);
		
	};

	renderAWidth = function(node,tree) {
		var depth, a = node.get(0).tagName.toLowerCase() === "a" ? node : node.children("a"),
		width = parseInt(tree.settings.grid.columns[0].width,10) + parseInt(tree._gridSettings.treeWidthDiff,10);
		// need to use a selector in jquery 1.4.4+
		depth = tree.get_node(node).parents.length;
		width = width - depth*getIndent(node,tree);
		a.css({width: width, "vertical-align": "top", "overflow":"hidden","float":"left"});
	};
	renderATitle = function(node,t,tree) {
		var a = node.get(0).tagName.toLowerCase() === "a" ? node : node.children("a"), title, col = tree.settings.grid.columns[0];
		// get the title
		title = "";
		if (col.title) {
			if (col.title === SPECIAL_TITLE) {
				title = tree.get_text(t);
			} else if (t.attr(col.title)) {
				title = t.attr(col.title);
			}
		}
		// strip out HTML
		title = title.replace(htmlstripre, '');
		if (title) {
			a.attr("title",title);
		}
	};

	$.jstree.defaults.grid = {
		width: 25
	};

	$.jstree.plugins.grid = function(options,parent) {
		this._initialize = function () {
			if (!this._initialized) {
				var s = this.settings.grid || {}, styles,
				gs = this._gridSettings = {
					columns : s.columns || [],
					treeClass : "jstree-grid-col-0",
					columnWidth : s.width,
					defaultConf : {display: "inline-block","*display":"inline","*+display":"inline","float":"left"},
					isThemeroller : !!this._data.themeroller,
					treeWidthDiff : 0,
					resizable : s.resizable,
					indent: 0
				};
			
				var msie = /msie/.test(navigator.userAgent.toLowerCase());
				if (msie) {
					var version = parseFloat(navigator.appVersion.split("MSIE")[1]);
					if (version < 8) {
						gs.defaultConf.display = "inline";
						gs.defaultConf.zoom = "1";
					}
				}
			
				// set up the classes we need
				if (!styled) {
					styled = true;
					styles = [
						'.jstree-grid-cell {padding-left: 4px; vertical-align: top; overflow:hidden;}',
						'.jstree-grid-separator {display: inline-block; border-width: 0 2px 0 0; *display:inline; *+display:inline; margin-right:0px;float:left;width:0px;}',
	          '.jstree-grid-header-cell {float: left;}',
						'.jstree-grid-header-themeroller {border: 0; padding: 1px 3px;}',
						'.jstree-grid-header-regular {background-color: #EBF3FD;}',
						'.jstree-grid-resizable-separator {cursor: col-resize;}',
						'.jstree-grid-separator-regular {border-color: #d0d0d0; border-style: solid;}',
						'.jstree-grid-cell-themeroller {border: none !important; background: transparent !important;}'
					];

					$('<style type="text/css">'+styles.join("\n")+'</style>').appendTo("head");
				}
			
				this._initialized = true;
			}
		};
		this.init = function (el,options) { 
			parent.init.call(this,el,options);
			this._initialize();
		};
		this.bind = function () {
			parent.bind.call(this);
			this._initialize();
			this.element.on("open_node.jstree create_node.jstree redraw.jstree clean_node.jstree change_node.jstree", $.proxy(function (e, data) { 
					var target = this.get_node(data || "#",true);
					this._prepare_grid(target);
				}, this))
			.on("loaded.jstree", $.proxy(function (e) {
				this._prepare_headers();
				this.element.trigger("loaded_grid.jstree");
				}, this))
			.on("move_node.jstree",$.proxy(function(e,data){
				var node = data.new_instance.element;
				renderAWidth(node,this);
				// check all the children, because we could drag a tree over
				node.find("li > a").each($.proxy(function(i,elm){
					renderAWidth($(elm),this);
				},this));
				
			},this));
			if (this._gridSettings.isThemeroller) {
				this.element
					.on("select_node.jstree",$.proxy(function(e,data){
						data.rslt.obj.children("a").nextAll("div").addClass("ui-state-active");
					},this))
					.on("deselect_node.jstree deselect_all.jstree",$.proxy(function(e,data){
						data.rslt.obj.children("a").nextAll("div").removeClass("ui-state-active");
					},this))
					.on("hover_node.jstree",$.proxy(function(e,data){
						data.rslt.obj.children("a").nextAll("div").addClass("ui-state-hover");
					},this))
					.on("dehover_node.jstree",$.proxy(function(e,data){
						data.rslt.obj.children("a").nextAll("div").removeClass("ui-state-hover");
					},this));
			}
		};
		this.teardown = function() {
			var gridparent = this.parent, container = this.element;
			container.detach();
			$("div.jstree-grid-wrapper",gridparent).remove();
			gridparent.append(container);
			parent.teardown.call(this);
		};
		this._prepare_headers = function() {
			var header, i, gs = this._gridSettings,cols = gs.columns || [], width, defaultWidth = gs.columnWidth, resizable = gs.resizable || false,
			cl, val, margin, last, tr = gs.isThemeroller, classAdd = (tr?"themeroller":"regular"),
			container = this.element, gridparent = container.parent(), hasHeaders = 0,
			conf = gs.defaultConf, isClickedSep = false, oldMouseX = 0, newMouseX = 0, currentTree = null, colNum = 0, toResize = null, clickedSep = null, borPadWidth = 0;
			// save the original parent so we can reparent on destroy
			this.parent = gridparent;
			
			
			// set up the wrapper, if not already done
			header = this.header || $("<div></div>").addClass((tr?"ui-widget-header ":"")+"jstree-grid-header jstree-grid-header-"+classAdd);
			
			// create the headers
			for (i=0;i<cols.length;i++) {
				cl = cols[i].headerClass || "";
				val = cols[i].header || "";
				if (val) {hasHeaders = true;}
				width = cols[i].width || defaultWidth;
				borPadWidth = tr ? 1+6 : 2+8; // account for the borders and padding
				width -= borPadWidth;
				margin = i === 0 ? 3 : 0;
				last = $("<div></div>").css(conf).css({"margin-left": margin,"width":width, "padding": "1 3 2 5"}).addClass((tr?"ui-widget-header ":"")+"jstree-grid-header jstree-grid-header-cell jstree-grid-header-"+classAdd+" "+cl).text(val).appendTo(header)
					.after("<div class='jstree-grid-separator jstree-grid-separator-"+classAdd+(tr ? " ui-widget-header" : "")+(resizable? " jstree-grid-resizable-separator":"")+"'>&nbsp;</div>");
			}
			if (last) {
				last.addClass((tr?"ui-widget-header ":"")+"jstree-grid-header jstree-grid-header-"+classAdd);
			}
			// add a clearer
			$("<div></div>").css("clear","both").appendTo(header);
			// did we have any real columns?
			if (hasHeaders) {
				$("<div></div>").addClass("jstree-grid-wrapper").appendTo(gridparent).append(header).append(container);
				// save the offset of the div from the body
				gs.divOffset = header.parent().offset().left;
				gs.header = header;
			}

			if (!bound && resizable) {
				bound = true;
				$(document).on("selectstart", ".jstree-grid-separator", function () { return false; });
				$(document).on("mousedown", ".jstree-grid-separator", function (e) {
						clickedSep = $(this);
						isClickedSep = true;
						currentTree = clickedSep.parents(".jstree-grid-wrapper").children(".jstree");
						oldMouseX = e.clientX;
						colNum = clickedSep.prevAll(".jstree-grid-header").length-1;
						toResize = clickedSep.prev().add(currentTree.find(".jstree-grid-col-"+colNum));
						return false;
					});
				$(document)
					.mouseup(function () {
						var  i, ref, cols, widths, headers, w;
						if (isClickedSep) {
							ref = $.jstree.reference(currentTree);
							cols = ref.settings.grid.columns;
							headers = clickedSep.parent().children(".jstree-grid-header");
							widths = [];
							if (isNaN(colNum) || colNum < 0) { ref._gridSettings.treeWidthDiff = currentTree.find("ins:eq(0)").width() + currentTree.find("a:eq(0)").width() - ref._gridSettings.columns[0].width; }
							isClickedSep = false;
							for (i=0;i<cols.length;i++) {
								w = parseFloat(headers[i].style.width)+borPadWidth;
								widths[i] = {w: w, r: i===colNum };
								ref._gridSettings.columns[i].width = w;
							}
							
							currentTree.trigger("resize_column.jstree-grid", widths);
						}
					})
					.mousemove(function (e) {
						if (isClickedSep) {
							newMouseX = e.clientX;
							var diff = newMouseX - oldMouseX;
							toResize.each(function () { this.style.width = parseFloat(this.style.width) + diff + "px"; });
							oldMouseX = newMouseX;
						}
					});
			}
		};
		/*
		 * Override redraw_node to correctly insert the grid
		 */
		this.redraw_node = function(obj, deep, is_callback) {
			// first allow the parent to redraw the node
			obj = parent.redraw_node.call(this, obj, deep, is_callback);
			// next prepare the grid
			if(obj) {
				this._prepare_grid(obj);
			}
			return obj;
		};
		this._prepare_grid = function (obj) {
			var gs = this._gridSettings, c = gs.treeClass, _this = this, t, cols = gs.columns || [], width, tr = gs.isThemeroller, 
			classAdd = (tr?"themeroller":"regular"), img, objData = this.get_node(obj),
			defaultWidth = gs.columnWidth, conf = gs.defaultConf, cellClickHandler = function (val,col,s) {
				return function() {
					$(this).trigger("select_cell.jstree-grid", [{value: val,column: col.header,node: $(this).closest("li"),sourceName: col.value,sourceType: s}]);
				};
			};
			// get our column definition
			t = $(obj);
			var i, val, cl, wcl, a, last, valClass, wideValClass, span, paddingleft, title, isAlreadyGrid, col, content, s, tmpWidth;
			
			// find the a children
			a = t.children("a");
			isAlreadyGrid = a.hasClass(c);
			
			if (a.length === 1) {
			  a.prev().css("float","left");
				a.addClass(c);
				renderAWidth(a,_this);
				renderATitle(a,t,_this);
				last = a;
				for (i=1;i<cols.length;i++) {
					col = cols[i];
					// get the cellClass and the wideCellClass
					cl = col.cellClass || "";
					wcl = col.wideCellClass || "";


					// get the contents of the cell - value could be a string or a function
					if (col.value !== undefined && col.value !== null && objData.data !== null && objData.data !== undefined) {
						if (typeof(col.value) === "function") {
							val = col.value(objData.data);
						} else if (objData.data[col.value] !== undefined) {
							val = objData.data[col.value];
						} else {
							val = "";
						}
					} else {
						val = "";
					}

					// put images instead of text if needed
					if (col.images) {
					img = col.images[val] || col.images["default"];
					if (img) {content = img[0] === "*" ? '<span class="'+img.substr(1)+'"></span>' : '<img src="'+img+'">';}
					} else { content = val; }

					// get the valueClass
					valClass = col.valueClass && t.attr(col.valueClass) ? t.attr(col.valueClass) : "";
					if (valClass && col.valueClassPrefix && col.valueClassPrefix !== "") {
						valClass = col.valueClassPrefix + valClass;
					}
					// get the wideValueClass
					wideValClass = col.wideValueClass && t.attr(col.wideValueClass) ? t.attr(col.wideValueClass) : "";
					if (wideValClass && col.wideValueClassPrefix && col.wideValueClassPrefix !== "") {
						wideValClass = col.wideValueClassPrefix + wideValClass;
					}
					// get the title
					title = objData.data.type;

					// strip out HTML
					title = title.replace(htmlstripre, '');
					
					// get the width
					paddingleft = 7;
					width = col.width || defaultWidth;
					//tmpWidth = $.support.boxModel ? $(".jstree-grid-col-"+i+":first",t).width() : $(".jstree-grid-col-"+i+":first",t).outerWidth();
					width = tmpWidth || (width - paddingleft);
					
					last = isAlreadyGrid ? a.nextAll("div:eq("+(i-1)+")") : $("<div></div>").insertAfter(last);
					span = isAlreadyGrid ? last.children("span") : $("<span></span>").appendTo(last);

					// create a span inside the div, so we can control what happens in the whole div versus inside just the text/background
					span.addClass(cl+" "+valClass).css({"margin-right":"0px","display":"inline-block","*display":"inline","*+display":"inline"}).html(content)
					// add click handler for clicking inside a grid cell
					.click(cellClickHandler(val,col,s));
					last = last.css(conf).css({width: width,"padding-left":paddingleft+"px"}).addClass("jstree-grid-cell jstree-grid-cell-"+classAdd+" "+wcl+ " " + wideValClass + (tr?" ui-state-default":"")).addClass("jstree-grid-col-"+i);
					
					if (title) {
						span.attr("title",title);
					}

				}		
				last.addClass("jstree-grid-cell-last"+(tr?" ui-state-default":""));
				$("<div></div>").css("clear","both").insertAfter(last);
			}
			this.element.css({'overflow-y':'auto !important'});			
		};

		// need to do alternating background colors or borders
	};
}(jQuery));