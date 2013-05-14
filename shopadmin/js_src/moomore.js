//MooTools More, <http://mootools.net/more>. Copyright (c) 2006-2008 Valerio Proietti, <http://mad4milk.net>, MIT Style License.

/*
Script: Drag.js
    The base Drag Class. Can be used to drag and resize Elements using mouse events.

License:
    MIT-style license.
*/

var Drag = new Class({

	Implements: [Events, Options],

	options: {/*
		onBeforeStart: $empty(thisElement),
		onStart: $empty(thisElement, event),
		onSnap: $empty(thisElement)
		onDrag: $empty(thisElement, event),
		onCancel: $empty(thisElement),
		onComplete: $empty(thisElement, event),*/
		snap: 6,
		unit: 'px',
		grid: false,
		style: true,
		limit: false,
		handle: false,
		invert: false,
		preventDefault: false,
		modifiers: {x: 'left', y: 'top'}
	},

	initialize: function(){
		var params = Array.link(arguments, {'options': Object.type, 'element': $defined});
		this.element = document.id(params.element);
		this.document = this.element.getDocument();
		this.setOptions(params.options || {});
		var htype = $type(this.options.handle);
		this.handles = ((htype == 'array' || htype == 'collection') ? $$(this.options.handle) : document.id(this.options.handle)) || this.element;
		this.mouse = {'now': {}, 'pos': {}};
		this.value = {'start': {}, 'now': {}};

		this.selection = (Browser.Engine.trident) ? 'selectstart' : 'mousedown';

		this.bound = {
			start: this.start.bind(this),
			check: this.check.bind(this),
			drag: this.drag.bind(this),
			stop: this.stop.bind(this),
			cancel: this.cancel.bind(this),
			eventStop: $lambda(false)
		};
		this.attach();
	},

	attach: function(){
		this.handles.addEvent('mousedown', this.bound.start);
		return this;
	},

	detach: function(){
		this.handles.removeEvent('mousedown', this.bound.start);
		return this;
	},

	start: function(event){
		if (this.options.preventDefault) event.preventDefault();
		this.mouse.start = event.page;
		this.fireEvent('beforeStart', this.element);
		var limit = this.options.limit;
		this.limit = {x: [], y: []};
		for (var z in this.options.modifiers){
			if (!this.options.modifiers[z]) continue;
			if (this.options.style) this.value.now[z] = this.element.getStyle(this.options.modifiers[z]).toInt();
			else this.value.now[z] = this.element[this.options.modifiers[z]];
			if (this.options.invert) this.value.now[z] *= -1;
			this.mouse.pos[z] = event.page[z] - this.value.now[z];
			if (limit && limit[z]){
				for (var i = 2; i--; i){
					if ($chk(limit[z][i])) this.limit[z][i] = $lambda(limit[z][i])();
				}
			}
		}
		if ($type(this.options.grid) == 'number') this.options.grid = {x: this.options.grid, y: this.options.grid};
		this.document.addEvents({mousemove: this.bound.check, mouseup: this.bound.cancel});
		this.document.addEvent(this.selection, this.bound.eventStop);
	},

	check: function(event){
		if (this.options.preventDefault) event.preventDefault();
		var distance = Math.round(Math.sqrt(Math.pow(event.page.x - this.mouse.start.x, 2) + Math.pow(event.page.y - this.mouse.start.y, 2)));
		if (distance > this.options.snap){
			this.cancel();
			this.document.addEvents({
				mousemove: this.bound.drag,
				mouseup: this.bound.stop
			});
			this.fireEvent('start', [this.element, event]).fireEvent('snap', this.element);
		}
	},

	drag: function(event){
		if (this.options.preventDefault) event.preventDefault();
		this.mouse.now = event.page;
		for (var z in this.options.modifiers){
			if (!this.options.modifiers[z]) continue;
			this.value.now[z] = this.mouse.now[z] - this.mouse.pos[z];
			if (this.options.invert) this.value.now[z] *= -1;
			if (this.options.limit && this.limit[z]){
				if ($chk(this.limit[z][1]) && (this.value.now[z] > this.limit[z][1])){
					this.value.now[z] = this.limit[z][1];
				} else if ($chk(this.limit[z][0]) && (this.value.now[z] < this.limit[z][0])){
					this.value.now[z] = this.limit[z][0];
				}
			}
			if (this.options.grid[z]) this.value.now[z] -= ((this.value.now[z] - (this.limit[z][0]||0)) % this.options.grid[z]);
			if (this.options.style) this.element.setStyle(this.options.modifiers[z], this.value.now[z] + this.options.unit);
			else this.element[this.options.modifiers[z]] = this.value.now[z];
		}
		this.fireEvent('drag', [this.element, event]);
	},

	cancel: function(event){
		this.document.removeEvent('mousemove', this.bound.check);
		this.document.removeEvent('mouseup', this.bound.cancel);
		if (event){
			this.document.removeEvent(this.selection, this.bound.eventStop);
			this.fireEvent('cancel', this.element);
		}
	},

	stop: function(event){
		this.document.removeEvent(this.selection, this.bound.eventStop);
		this.document.removeEvent('mousemove', this.bound.drag);
		this.document.removeEvent('mouseup', this.bound.stop);
		if (event) this.fireEvent('complete', [this.element, event]);
	}

});

Element.implement({

	makeResizable: function(options){
		var drag = new Drag(this, $merge({modifiers: {x: 'width', y: 'height'}}, options));
		this.store('resizer', drag);
		return drag.addEvent('drag', function(){
			this.fireEvent('resize', drag);
		}.bind(this));
	}

});


/*
Script: Fx.Scroll.js
    Effect to smoothly scroll any element, including the window.

License:
    MIT-style license.
*/

Fx.Scroll = new Class({

    Extends: Fx,

    options: {
        offset: {'x': 0, 'y': 0},
        wheelStops: true
    },

    initialize: function(element, options){
        this.element = this.subject = $(element);
        this.parent(options);
        var cancel = this.cancel.bind(this, false);

        if ($type(this.element) != 'element') this.element = $(this.element.getDocument().body);

        var stopper = this.element;

        if (this.options.wheelStops){
            this.addEvent('start', function(){
                stopper.addEvent('mousewheel', cancel);
            }, true);
            this.addEvent('complete', function(){
                stopper.removeEvent('mousewheel', cancel);
            }, true);
        }
    },

    set: function(){
        var now = Array.flatten(arguments);
        this.element.scrollTo(now[0], now[1]);
    },

    compute: function(from, to, delta){
        var now = [];
        var x = 2;
        x.times(function(i){
            now.push(Fx.compute(from[i], to[i], delta));
        });
        return now;
    },

    start: function(x, y){
        if (!this.check(arguments.callee, x, y)) return this;
        var offsetSize = this.element.getSize(), scrollSize = this.element.getScrollSize();
        var scroll = this.element.getScroll(), values = {x: x, y: y};
        for (var z in values){
            var max = scrollSize[z] - offsetSize[z];
            if ($chk(values[z])) values[z] = ($type(values[z]) == 'number') ? values[z].limit(0, max) : max;
            else values[z] = scroll[z];
            values[z] += this.options.offset[z];
        }
        return this.parent([scroll.x, scroll.y], [values.x, values.y]);
    },

    toTop: function(){
        return this.start(false, 0);
    },

    toLeft: function(){
        return this.start(0, false);
    },

    toRight: function(){
        return this.start('right', false);
    },

    toBottom: function(){
        return this.start(false, 'bottom');
    },

    toElement: function(el){
        var position = $(el).getPosition(this.element);
        return this.start(position.x, position.y);
    }

});


/*
Script: Tips.js
    Class for creating nice tips that follow the mouse cursor when hovering an element.

License:
    MIT-style license.
*/

var Tips = new Class({

    Implements: [Events, Options],

    options: {
        onShow: function(tip){
            tip.setStyle('visibility', 'visible');
        },
        onHide: function(tip){
            tip.setStyle('visibility', 'hidden');
        },
        showDelay: 100,
        hideDelay: 100,
        className: null,
        offsets: {x: 16, y: 16},
        fixed: false
    },

    initialize: function(){
        var params = Array.link(arguments, {options: Object.type, elements: $defined});
        this.setOptions(params.options || null);
        
        this.tip = new Element('div').inject(document.body);
        
        if (this.options.className) this.tip.addClass(this.options.className);
        
        var top = new Element('div', {'class': 'tip-top'}).inject(this.tip);
        this.container = new Element('div', {'class': 'tip'}).inject(this.tip);
        var bottom = new Element('div', {'class': 'tip-bottom'}).inject(this.tip);

        this.tip.setStyles({position: 'absolute', top: 0, left: 0, visibility: 'hidden'});
        
        if (params.elements) this.attach(params.elements);
    },
    
    attach: function(elements){
        $$(elements).each(function(element){
            var title = element.retrieve('tip:title', element.get('title'));
            var text = element.retrieve('tip:text', element.get('rel') || element.get('href'));
            var enter = element.retrieve('tip:enter', this.elementEnter.bindWithEvent(this, element));
            var leave = element.retrieve('tip:leave', this.elementLeave.bindWithEvent(this, element));
            element.addEvents({mouseenter: enter, mouseleave: leave});
            if (!this.options.fixed){
                var move = element.retrieve('tip:move', this.elementMove.bindWithEvent(this, element));
                element.addEvent('mousemove', move);
            }
            element.store('tip:native', element.get('title'));
            element.erase('title');
        }, this);
        return this;
    },
    
    detach: function(elements){
        $$(elements).each(function(element){
            element.removeEvent('mouseenter', element.retrieve('tip:enter') || $empty);
            element.removeEvent('mouseleave', element.retrieve('tip:leave') || $empty);
            element.removeEvent('mousemove', element.retrieve('tip:move') || $empty);
            element.eliminate('tip:enter').eliminate('tip:leave').eliminate('tip:move');
            var original = element.retrieve('tip:native');
            if (original) element.set('title', original);
        });
        return this;
    },
    
    elementEnter: function(event, element){
        
        $A(this.container.childNodes).each(Element.dispose);
        
        var title = element.retrieve('tip:title');
        
        if (title){
            this.titleElement = new Element('div', {'class': 'tip-title'}).inject(this.container);
            this.fill(this.titleElement, title);
        }
        
        var text = element.retrieve('tip:text');
        if (text){
            this.textElement = new Element('div', {'class': 'tip-text'}).inject(this.container);
            this.fill(this.textElement, text);
        }
        
        this.timer = $clear(this.timer);
        this.timer = this.show.delay(this.options.showDelay, this);

        this.position((!this.options.fixed) ? event : {page: element.getPosition()});
    },
    
    elementLeave: function(event){
        $clear(this.timer);
        this.timer = this.hide.delay(this.options.hideDelay, this);
    },
    
    elementMove: function(event){
        this.position(event);
    },
    
    position: function(event){
        var size = window.getSize(), scroll = window.getScroll();
        var tip = {x: this.tip.offsetWidth, y: this.tip.offsetHeight};
        var props = {x: 'left', y: 'top'};
        for (var z in props){
            var pos = event.page[z] + this.options.offsets[z];
            if ((pos + tip[z] - scroll[z]) > size[z]) pos = event.page[z] - this.options.offsets[z] - tip[z];
            this.tip.setStyle(props[z], pos);
        }
    },
    
    fill: function(element, contents){
        (typeof contents == 'string') ? element.set('html', contents) : element.adopt(contents);
    },

    show: function(){
        this.fireEvent('show', this.tip);
    },

    hide: function(){
        this.fireEvent('hide', this.tip);
    }

});

/*
Script: Drag.Move.js
	A Drag extension that provides support for the constraining of draggables to containers and droppables.

	License:
		MIT-style license.

	Authors:
		Valerio Proietti
		Tom Occhinno
		Jan Kassens*/

Drag.Move = new Class({

	Extends: Drag,

	options: {/*
		onEnter: $empty(thisElement, overed),
		onLeave: $empty(thisElement, overed),
		onDrop: $empty(thisElement, overed, event),*/
		droppables: [],
		container: false,
		precalculate: false,
		includeMargins: true,
		checkDroppables: true
	},

	initialize: function(element, options){
		this.parent(element, options);
		this.droppables = $$(this.options.droppables);
		this.container = document.id(this.options.container);
		if (this.container && $type(this.container) != 'element') this.container = document.id(this.container.getDocument().body);

		var position = this.element.getStyle('position');
		if (position=='static') position = 'absolute';
		if ([this.element.getStyle('left'), this.element.getStyle('top')].contains('auto')) this.element.position(this.element.getPosition(this.element.offsetParent));
		this.element.setStyle('position', position);

		this.addEvent('start', this.checkDroppables, true);

		this.overed = null;
	},

	start: function(event){
		if (this.container){
			var ccoo = this.container.getCoordinates(this.element.getOffsetParent()), cbs = {}, ems = {};

			['top', 'right', 'bottom', 'left'].each(function(pad){
				cbs[pad] = this.container.getStyle('border-' + pad).toInt();
				ems[pad] = this.element.getStyle('margin-' + pad).toInt();
			}, this);

			var width = this.element.offsetWidth + ems.left + ems.right;
			var height = this.element.offsetHeight + ems.top + ems.bottom;

			if (this.options.includeMargins) {
				$each(ems, function(value, key) {
					ems[key] = 0;
				});
			}
			if (this.container == this.element.getOffsetParent()) {
				this.options.limit = {
					x: [0 - ems.left, ccoo.right - cbs.left - cbs.right - width + ems.right],
					y: [0 - ems.top, ccoo.bottom - cbs.top - cbs.bottom - height + ems.bottom]
				};
			} else {
				this.options.limit = {
					x: [ccoo.left + cbs.left - ems.left, ccoo.right - cbs.right - width + ems.right],
					y: [ccoo.top + cbs.top - ems.top, ccoo.bottom - cbs.bottom - height + ems.bottom]
				};
			}

		}
		if (this.options.precalculate){
			this.positions = this.droppables.map(function(el) {
				return el.getCoordinates();
			});
		}
		this.parent(event);
	},

	checkAgainst: function(el, i){

		el = (this.positions) ? this.positions[i] : el.getCoordinates();
		var now = this.mouse.now;
		return (now.x > el.left && now.x < el.right && now.y < el.bottom && now.y > el.top);
	},

	checkDroppables: function(){
		var overed = this.droppables.filter(this.checkAgainst, this).getLast();
		if (this.overed != overed){
			if (this.overed) this.fireEvent('leave', [this.element, this.overed]);
			if (overed) this.fireEvent('enter', [this.element, overed]);
			this.overed = overed;
		}
	},

	drag: function(event){
		this.parent(event);
		if (this.options.checkDroppables && this.droppables.length) this.checkDroppables();
	},

	stop: function(event){
		this.checkDroppables();
		this.fireEvent('drop', [this.element, this.overed, event]);
		this.overed = null;
		return this.parent(event);
	}

});

Element.implement({

	makeDraggable: function(options){
		var drag = new Drag.Move(this, options);
		this.store('dragger', drag);
		return drag;
	}

});

Element.implement({

    makeDraggable: function(options){
        return new Drag.Move(this, options);
    }

});


/*
Script: Sortables.js
	Class for creating a drag and drop sorting interface for lists of items.

	License:
		MIT-style license.

	Authors:
		Tom Occhino
*/

var Sortables = new Class({

	Implements: [Events, Options],

	options: {/*
		onSort: $empty(element, clone),
		onStart: $empty(element, clone),
		onComplete: $empty(element),*/
		snap: 4,
		opacity: 1,
		clone: false,
		revert: false,
		handle: false,
		constrain: false,
        relativeBox:false
	},

	initialize: function(lists, options){
		this.setOptions(options);
		this.elements = [];
		this.lists = [];
		this.idle = true;

		this.addLists($$(document.id(lists) || lists));
		if (!this.options.clone) this.options.revert = false;
		if (this.options.revert) this.effect = new Fx.Morph(null, $merge({duration: 250, link: 'cancel'}, this.options.revert));
	},

	attach: function(){
		this.addLists(this.lists);
		return this;
	},

	detach: function(){
		this.lists = this.removeLists(this.lists);
		return this;
	},

	addItems: function(){
		Array.flatten(arguments).each(function(element){
			this.elements.push(element);
			var start = element.retrieve('sortables:start', this.start.bindWithEvent(this, element));
			(this.options.handle ? element.getElement(this.options.handle) || element : element).addEvent('mousedown', start);
		}, this);
		return this;
	},

	addLists: function(){
		Array.flatten(arguments).each(function(list){
			this.lists.push(list);
			this.addItems(list.getChildren());
		}, this);
		return this;
	},

	removeItems: function(){
		return $$(Array.flatten(arguments).map(function(element){
			this.elements.erase(element);
			var start = element.retrieve('sortables:start');
			(this.options.handle ? element.getElement(this.options.handle) || element : element).removeEvent('mousedown', start);
			
			return element;
		}, this));
	},

	removeLists: function(){
		return $$(Array.flatten(arguments).map(function(list){
			this.lists.erase(list);
			this.removeItems(list.getChildren());
			
			return list;
		}, this));
	},

	getClone: function(event, element){
		if (!this.options.clone) return new Element('div').inject(document.body);
		if ($type(this.options.clone) == 'function') return this.options.clone.call(this, event, element, this.list);
		return element.clone(true).setStyles({
			margin: '0px',
			position: 'absolute',
			visibility: 'hidden',
			'width': element.getStyle('width')
		}).inject(this.list).position(element.getPosition(this.options.relativeBox||element.getOffsetParent()));
	},

	getDroppables: function(){
		var droppables = this.list.getChildren();
		if (!this.options.constrain) droppables = this.lists.concat(droppables).erase(this.list);
		return droppables.erase(this.clone).erase(this.element);
	},

	insert: function(dragging, element){
		var where = 'inside';
		if (this.lists.contains(element)){
			this.list = element;
			this.drag.droppables = this.getDroppables();
		} else {
			where = this.element.getAllPrevious().contains(element) ? 'before' : 'after';
		}
		this.element.inject(element, where);
		this.fireEvent('sort', [this.element, this.clone]);
	},

	start: function(event, element){
		if (!this.idle) return;
		this.idle = false;
		this.element = element;
		this.opacity = element.get('opacity');
		this.list = element.getParent();
		this.clone = this.getClone(event, element);

		this.drag = new Drag.Move(this.clone, {
			snap: this.options.snap,
			container: this.options.constrain && this.element.getParent(),
			droppables: this.getDroppables(),
			onSnap: function(){
				event.stop();
				this.clone.setStyle('visibility', 'visible');
				this.element.set('opacity', this.options.opacity || 0);
				this.fireEvent('start', [this.element, this.clone]);
			}.bind(this),
			onEnter: this.insert.bind(this),
			onCancel: this.reset.bind(this),
			onComplete: this.end.bind(this)
		});

		this.clone.inject(this.element, 'before');
		this.drag.start(event);
	},

	end: function(){
		this.drag.detach();
		this.element.set('opacity', this.opacity);
		if (this.effect){
			var dim = this.element.getStyles('width', 'height');
			var pos = this.clone.computePosition(this.element.getPosition(this.clone.offsetParent));
			this.effect.element = this.clone;
			this.effect.start({
				top: pos.top,
				left: pos.left,
				width: dim.width,
				height: dim.height,
				opacity: 0.25
			}).chain(this.reset.bind(this));
		} else {
			this.reset();
		}
	},

	reset: function(){
		this.idle = true;
		this.clone.destroy();
		this.fireEvent('complete', this.element);
	},

	serialize: function(){
		var params = Array.link(arguments, {modifier: Function.type, index: $defined});
		var serial = this.lists.map(function(list){
			return list.getChildren().map(params.modifier || function(element){
				return element.get('id');
			}, this);
		}, this);

		var index = params.index;
		if (this.lists.length == 1) index = 0;
		return $chk(index) && index >= 0 && index < this.lists.length ? serial[index] : serial;
	}

});

/*
Script: Assets.js
    Provides methods to dynamically load JavaScript, CSS, and Image files into the document.

License:
    MIT-style license.
*/

var Asset = new Hash({

    javascript: function(source, properties){
        properties = $extend({
            onload: $empty,
            document: document,
            check: $lambda(true)
        }, properties);
        
        var script = new Element('script', {'src': source, 'type': 'text/javascript'});
        
        var load = properties.onload.bind(script), check = properties.check, doc = properties.document;
        delete properties.onload; delete properties.check; delete properties.document;
        
        script.addEvents({
            load: load,
            readystatechange: function(){
                if (['loaded', 'complete'].contains(this.readyState)) load();
            }
        }).setProperties(properties);
        
        
        if (Browser.Engine.webkit419) var checker = (function(){
            if (!$try(check)) return;
            $clear(checker);
            load();
        }).periodical(50);
        
        return script.inject(doc.head);
    },

    css: function(source, properties){
        return new Element('link', $merge({
            'rel': 'stylesheet', 'media': 'screen', 'type': 'text/css', 'href': source
        }, properties)).inject(document.head);
    },

    image: function(source, properties){
        properties = $merge({
            'onload': $empty,
            'onabort': $empty,
            'onerror': $empty
        }, properties);
        var image = new Image();
        var element = $(image) || new Element('img');
        ['load', 'abort', 'error'].each(function(name){
            var type = 'on' + name;
            var event = properties[type];
            delete properties[type];
            image[type] = function(){
                if (!image) return;
                if (!element.parentNode){
                    element.width = image.width;
                    element.height = image.height;
                }
                image = image.onload = image.onabort = image.onerror = null;
                event.delay(1, element, element);
                element.fireEvent(name, element, 1);
            };
        });
        image.src = element.src = source;
        if (image && image.complete) image.onload.delay(1);
        return element.setProperties(properties);
    },

    images: function(sources, options){
        options = $merge({
            onComplete: $empty,
            onProgress: $empty
        }, options);
        if (!sources.push) sources = [sources];
        var images = [];
        var counter = 0;
        sources.each(function(source){
            var img = new Asset.image(source, {
                'onload': function(){
                    options.onProgress.call(this, counter, sources.indexOf(source));
                    counter++;
                    if (counter == sources.length) options.onComplete();
                }
            });
            images.push(img);
        });
        return new Elements(images);
    }

});


Class.refactor = function(original, refactors){

	$each(refactors, function(item, name){
		var origin = original.prototype[name];
		if (origin && (origin = origin._origin) && typeof item == 'function') original.implement(name, function(){
			var old = this.previous;
			this.previous = origin;
			var value = item.apply(this, arguments);
			this.previous = old;
			return value;
		}); else original.implement(name, item);
	});

	return original;

};
