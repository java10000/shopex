Element.extend({
    amongTo: function(elp, opts) {
        var el = this;
        var elSize = el.getSize();
        var elpSize = elp.getSize();
        var options = {
            width: 2,
            height: 2
        };
        if (opts) {
            options = $merge(options, opts);
        }
        el.setStyles({
            'position': 'absolute',
            'opacity': 1,
            'visibility': 'visible',
            'display': ''
        });
        var pos = {
            'top': Math.abs(((elpSize.size.y / options.height).toInt()) - ((elSize.size.y / options.height).toInt()) + elp.getPosition().y + elpSize.scroll.y),
            'left': Math.abs(((elpSize.size.x / options.width).toInt()) - ((elSize.size.x / options.width).toInt()) + elp.getPosition().x + elpSize.scroll.x)
        };
        if (options.Fx) {
            new Fx.Styles(el).start(pos);
        } else {
            el.setStyles(pos);
        }
        return this;
    },
    zoomImg: function(maxwidth, maxheight, v) {
        if (this.getTag() != 'img' || ! this.width) return null;
        var thisSize = {
            'width': this.width,
            'height': this.height
        };
        var overSize, zoomSizeW, zoomSizeH, zoomC;
        if (thisSize.width > maxwidth) {
            overSize = thisSize.width - maxwidth;
            zoomSizeW = thisSize.width - overSize;
            zoomC = (zoomSizeW / thisSize.width).toFloat();
            zoomSizeH = (thisSize.height * zoomC).toInt();
            $extend(thisSize, {
                'width': zoomSizeW,
                'height': zoomSizeH
            });
        }
        if (thisSize.height > maxheight) {
            overSize = thisSize.height - maxheight;
            zoomSizeH = thisSize.height - overSize;
            zoomC = (zoomSizeH / thisSize.height).toFloat();
            zoomSizeW = (thisSize.width * zoomC).toInt();
            $extend(thisSize, {
                'width': zoomSizeW,
                'height': zoomSizeH
            });
        }
        if (!v) return this.set(thisSize);
        return thisSize;
    },
    getPadding: function() {
        return {
            'x': (this.getStyle('padding-left').toInt() || 0) + (this.getStyle('padding-right').toInt() || 0),
            'y': (this.getStyle('padding-top').toInt() || 0) + (this.getStyle('padding-bottom').toInt() || 0)
        };
    },
    getMargin: function() {
        return {
            'x': (this.getStyle('margin-left').toInt() || 0) + (this.getStyle('margin-right').toInt() || 0),
            'y': (this.getStyle('margin-top').toInt() || 0) + (this.getStyle('margin-bottom').toInt() || 0)
        };
    },
    getBorderWidth: function() {
        return {
            'left': this.getStyle('border-left-width').toInt() || 0,
            'right': this.getStyle('border-right-width').toInt() || 0,
            'top': this.getStyle('border-top-width').toInt() || 0,
            'bottom': this.getStyle('border-bottom-width').toInt() || 0,
            'x': (this.getStyle('border-left-width').toInt() || 0) + (this.getStyle('border-right-width').toInt() || 0),
            'y': (this.getStyle('border-top-width').toInt() || 0) + (this.getStyle('border-bottom-width').toInt() || 0)
        };
    },
    getTrueWidth: function() {
        return this.getSize().size.x - (this.getMargin().x + this.getPadding().x + this.getBorderWidth().left + this.getBorderWidth().right);
    },
    getTrueHeight: function() {
        return this.getSize().size.y - (this.getMargin().y + this.getPadding().y + this.getBorderWidth().top + this.getBorderWidth().bottom);
    },
    getCis: function() {
        return this.getCoordinates();
    },
    bindValidator: function(vipt_className) {
        vipt_className = vipt_className || 'x-input';
        var status = true;
        var errorIndex = [];
        var f = this;
        elements = f.getElements('.' + vipt_className);
        if (vipt_className != "_x_ipt") {
            elements.combine(f.getElements('._x_ipt'));
        }
        if (!elements || ! elements.length) return status;
        elements.each(function(el, idx) {
            if (el.get('emptyText')) {
                Element.emptyText(el);
            }
            var vv = validator.test(f, el);

            if (!vv) {
                errorIndex.push(idx);
                status = false;
            }
        });
        if (!status) {
            var el = elements[errorIndex[0]];
            try {
                if (el && el.getStyle('display') != 'none' && el.getStyle('visibility') != 'hidden') el.focus();
            } catch(e) {}
        }
        return status;
    },
    show: function(display) {
        this.fireEvent('onshow', this);
        return $chk(this) ? this.setStyle('display', display || '') : this;
    },
    hide: function() {
        this.fireEvent('onhide', this);
        return $chk(this) ? this.setStyle('display', 'none') : this;
    },
    isDisplay: function() {
        return this.getStyle('display') != 'none' && (this.offsetWidth + this.offsetHeight) !== 0;
    },
    toggleDisplay: function(display) {
        return $chk(this) && this.getStyle('display') == 'none' ? this.setStyle('display', display || '') : this.setStyle('display', 'none');
    }
});
String.extend({
    format: function() {
        if (arguments.length === 0) return this;
        var reg = /{(\d+)?}/g;
        var args = arguments;
        var string = this;
        var result = this.replace(reg, function($0, $1) {
            return args[$1.toInt()] || "";
        });
        return result;
    }
});

(function() {
    broswerStore = null;
    withBroswerStore = function(callback) {
        if (broswerStore) return callback(broswerStore);
        window.addEvent('domready', function() {
            if ((broswerStore = new BrowserStore())) {
                callback(broswerStore);
            } else {
                window.addEvent('load', function() {
                    callback(broswerStore = new BrowserStore());
                });
            }
        });
    };
})();

var ItemAgg = new Class({
    Implements: [Events, Options],
    options: {
        /*onActive: $empty,
        onBackground: $empty,*/
        show: 0,
        eventName: 'click',
        activeName: 'cur',
        firstShow: true
    },
    initialize: function(tabs, items, options) {
        this.setOptions(options);
        this.tabs = $$(tabs);
        this.items = $$(items);
        this.items.setStyle('display', 'none');
        this.tempCurIndex = this.options.show || 0;

        if (this.options.firstShow) {
            this.show(this.tempCurIndex);
        }
        this.tabs.each(function(item, index) {
            item.addEvent(this.options.eventName, function(e) {
                e = new Event(e).stop();
                this.tabs[index].blur();
                if (this.tempCurIndex != index) {
                    this.show(index);
                    this.hide(this.tempCurIndex);
                    this.tempCurIndex = index;
                }
            }.bind(this));
        }, this);
    },
    show: function(index) {
        this.items[index].show();
        this.tabs[index].addClass(this.options.activeName);
        this.fireEvent('onActive', [this.tabs[index], this.items[index]], this);
    },
    hide: function(index) {
        this.items[index].hide();
        this.tabs[index].removeClass(this.options.activeName);
        this.fireEvent('onBackground', [this.tabs[index], this.items[index]], this);
    }
});

var MessageBox = new Class({
    options: {
        delay: 1,
        // onFlee: $empty,
        FxOptions: {}
    },
    initialize: function(msg, type, options) {
        $extend(this.options, options);
        this.createBox(msg, type);

    },
    flee: function(mb) {
        var mbFx = new Fx.Styles(mb, this.options.FxOptions);
        var begin = false;
        var obj = this;
        mb.addEvents({
            mouseenter: function() {
                if (begin) mbFx.pause();
            },
            mouseleave: function() {
                if (begin) mbFx.resume();
            }
        });
        (function() {
            begin = true;
            mbFx.start({
                'left': 0,
                'opacity': 0
            }).chain(function() {
                this.element.remove();
                begin = false;

                if (obj.options.onFlee) {
                    obj.options.onFlee.apply(obj, [obj]);
                }
                if (window.MessageBoxOnFlee) {
                    window.MessageBoxOnFlee();
                    window.MessageBoxOnFlee = null;
                }
            });
        }).delay(this.options.delay * 1000);

    },
    createBox: function(msg, type) {
        var msgCLIP = /<h4[^>]*>([\s\S]*?)<\/h4>/;
        var tempmsg = msg;
        if (tempmsg.test(msgCLIP)) {
            tempmsg.replace(msgCLIP, function() {
                msg = arguments[1];
                return '';
            });
        }
        var box = new Element('div').setStyles({
            'position': 'absolute',
            'visibility': 'hidden',
            'width': 200,
            'opacity': 0,
            'zIndex': 65535
        }).inject(document.body);
        var obj = this;

        box.addClass(type).setHTML("<h4>", msg, "</h4>").amongTo(window).effect('opacity').start(1).chain(function() {
            obj.flee(this.element);
        });
        return box;
    }
});

MessageBox.success = function(msg, options) {
    return new MessageBox(msg || "操作成功!", 'success', options);
};
MessageBox.error = function(msg, options) {
    return new MessageBox(msg || "操作失败!", 'error', options);
};
MessageBox.show = function(msg, options) {
    if (msg.contains('failedSplash')) {
        return new MessageBox(msg || "操作失败!", 'error', options);
    }
    return new MessageBox(msg || "操作成功!", 'success', options);

};

var QMenu = new Class({
    Implements: [Events, Options],
    options: {
        overshow: true,
        delay: 500,
        lazyload: false
    },
    initialize: function(base, menu, options) {
        this.setOptions(options);
        this.base = $(base);
        this.menu = $(menu);
        if (this.options.overshow) {
            this.base.addEvent('mouseover', this.show.bind(this));
        } else {
            this.base.addEvent('click', this.show.bind(this));
        }
    },
    show: function(e) {
        if (!this.showing) {
            this.showing = true;
            if (this.options.lazyload && this.menu.getElement('textarea[lazyload]')) {
                var str = this.menu.getElement('textarea[lazyload]').get('value');
                this.menu.set('html', str);
            }
            if (!this.moreOnce) {
                this.moreOnce = true;
                this.fireEvent('firstshow', {
                    target: this.menu,
                    type: 'first'
                });

                if (this.options.overshow) {
                    this.menu.addEvents({
                        'mouseover': function() {
                            this.overmenu = true;
                        }.bind(this),
                        'mouseout': function() {
                            this.overmenu = false;
                            this.hide.delay(this.options.delay, this);
                        }.bind(this)
                    });
                    this.base.addEvent('mouseout', function() {
                        this.overbase = false;
                        this.hide.delay(this.options.delay, this);
                    }.bind(this));
                }

            }
            this.menu.setStyle('display', '');
            this.fireEvent('show', {
                target: this.menu,
                type: 'show'
            });
        }
        if (this.options.overshow) {
            this.overbase = true;
        }
    },
    hide: function() {
        if (this.showing) {
            if (this.options.overshow && (this.overbase || this.overmenu)) {
                return;
            }

            this.showing = false;
            this.menu.setStyle('display', 'none');
            this.fireEvent('hide', {
                target: this.menu,
                type: 'hide'
            });
        }
    }
});

_open = function(url, options) {
    options = $extend({
        width: window.getSize().x * 0.8,
        height: window.getSize().y
    }, options || {});
    var params = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width={width},height={height}';
    params = params.substitute(options);

    window.open(url || 'about:blank', '_blank', params);

};

/*fix Image size*/
var fixProductImageSize = function(images, ptag) {
    if (!images || ! images.length) return;
    images.each(function(img) {
        if (!img.src) return;
        new Asset.image(img.src, {
            onload: function() {
                var imgparent = img.getParent((ptag || 'a'));
                if (!this || ! this.get('width')) return imgparent.adopt(img);
                var imgpsize = {
                    x: imgparent.getTrueWidth(),
                    y: imgparent.getTrueHeight()
                };
                var nSize = this.zoomImg(imgpsize.x, imgpsize.y, true);
                img.set(nSize);
                var _style = {
                    'margin-top': ''
                };
                if (img && img.get('height') && img.get('height').toInt() < imgpsize.y) {
                    _style = $merge(_style, {
                        'margin-top': (imgpsize.y - img.get('height').toInt()) / 2
                    });
                }
                img.setStyles(_style);
                return true;
            }
        });
    });
};

