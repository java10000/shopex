(function() {

if (!window.localStorage) {
  Object.defineProperty(window, "localStorage", new (function () {
    var aKeys = [], oStorage = {};
    Object.defineProperty(oStorage, "getItem", {
      value: function (sKey) { return sKey ? this[sKey] : null; },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "key", {
      value: function (nKeyId) { return aKeys[nKeyId]; },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "setItem", {
      value: function (sKey, sValue) {
        if(!sKey) { return; }
        document.cookie = escape(sKey) + "=" + escape(sValue) + "; expires=Tue, 19 Jan 2038 03:14:07 GMT; path=/";
      },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "length", {
      get: function () { return aKeys.length; },
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "removeItem", {
      value: function (sKey) {
        if(!sKey) { return; }
        document.cookie = escape(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
      },
      writable: false,
      configurable: false,
      enumerable: false
    });
    this.get = function () {
      var iThisIndx;
      for (var sKey in oStorage) {
        iThisIndx = aKeys.indexOf(sKey);
        if (iThisIndx === -1) { oStorage.setItem(sKey, oStorage[sKey]); }
        else { aKeys.splice(iThisIndx, 1); }
        delete oStorage[sKey];
      }
      for (aKeys; aKeys.length > 0; aKeys.splice(0, 1)) { oStorage.removeItem(aKeys[0]); }
      for (var aCouple, iKey, nIdx = 0, aCouples = document.cookie.split(/\s*;\s*/); nIdx < aCouples.length; nIdx++) {
        aCouple = aCouples[nIdx].split(/\s*=\s*/);
        if (aCouple.length > 1) {
          oStorage[iKey = unescape(aCouple[0])] = unescape(aCouple[1]);
          aKeys.push(iKey);
        }
      }
      return oStorage;
    };
    this.configurable = false;
    this.enumerable = true;
  })());
}

    var LocalStorage = {
        name: 'localStorage',
        init: function() {
            if (!window.localStorage) {
                return false;
            }
            this._storage = window.localStorage;
            return this;
        },
        setStorage: function(key, value) {
            this._storage.setItem(key, value);
            return true;
        },
        getStorage: function(key, callback) {
            var item = this._storage.getItem(key);
            var value = item ? item: null;
            callback(value);
        },
        removeStorage: function(key) {
            this._storage.removeItem(key);
            return true;
        },
        clearStorage: function() {
            if (this._storage.clear) {
                this._storage.clear();
            } else {
                for (i in this._storage) {
                    if (this._storage[i].value) {
                        this.remove(i);
                    }
                }
            }
            return true;
        }

    };

    var GlobalStorage = {
        name: 'globalStorage',
        init: function() {
            if (!Browser.Engines.gecko || !window.globalStorage) {
                return false;
            }
            this._storage = globalStorage[location.hostname];
            return this;
        },
        setStorage: function(key, value) {
            this._storage.setItem(key, value);
            return true;
        },
        getStorage: function(key, callback) {
            var item = this._storage.getItem(key);
            var value = item ? item.value: null;
            callback(value);
        },
        removeStorage: function(key) {
            this._storage.removeItem(key);
            return true;
        },
        clearStorage: function() {
            if (this._storage.clear) {
                this._storage.clear();
            } else {
                for (i in this._storage) {
                    if (this._storage[i].value) {
                        this.remove(i);
                    }
                }
            }
            return true;
        }
    };

    var UserData = {
        name: 'userdata',
        init: function() {
            this.Master = "ie6+";
            if (!Browser.Engines.trident) return false;
            this._storage = new Element('span').setStyles({
                'display': 'none',
                'behavior': "url('#default#userData')"
            }).inject(document.body);
            return this;
        },
        setStorage: function(key, value) {
            this._storage.setAttribute(key, value);
            this._storage.save('shopEX_VS');
            return true;
        },
        getStorage: function(key, callback) {
            this._storage.load('shopEX_VS');
            callback(this._storage.getAttribute(key));
        },
        removeStorage: function(key) {
            this._storage.removeAttribute(key);
            this._storage.save('shopEX_VS');
            return true;
        },
        clearStorage: function() {
            var date = new Date();
            date.setMinutes(date.getMinutes() - 1);
            this._storage.expires = date.toUTCString();
            this._storage.save("shopEX_VS");
            this._storage.load("shopEX_VS");
            return true;
        }
    };

    var OpenDatabase = {
        name: 'openDatabase',
        
        init: function() {
            if (!window.openDatabase) return false;
            
            this._storage = window.openDatabase("viewState", "1.0", "ShopEX48 ViewState Storage", 20000);
            
            if ( 'undefined' == typeof this._storage.transaction) {
                this._storage = {transaction:function(){}};
            }
            
            this._createTable();
            return this;
        },
        setStorage: function(key, value) {
            this._storage.transaction(function(tx) {
                tx.executeSql("SELECT v FROM SessionStorage WHERE k = ?", [key], function(tx, result) {
                    if (result.rows.length) {
                        tx.executeSql("UPDATE SessionStorage SET v = ?  WHERE k = ?", [value, key]);
                    } else {
                        tx.executeSql("INSERT INTO SessionStorage (k, v) VALUES (?, ?)", [key, value]);
                    }
                });
            });
            return true;
        },
        getStorage: function(key, callback) {
            this._storage.transaction(function(tx) {
                v = tx.executeSql("SELECT v FROM SessionStorage WHERE k = ?", [key], function(tx, result) {
                    if (result.rows.length) return callback(result.rows.item(0).v);
                    callback(null);
                });
            });
        },
        removeStorage: function(key) {
            this._storage.transaction(function(tx) {
                tx.executeSql("DELETE FROM SessionStorage WHERE k = ?", [key]);
            });
            return true;
        },
        clearStorage: function() {
            this._storage.transaction(function(tx) {
                tx.executeSql("DROP TABLE SessionStorage", []);
            });
            return true;
        },
        _createTable: function() {
            this._storage.transaction(function(tx) {
                tx.executeSql("SELECT COUNT(*) FROM SessionStorage", [], function() {},
                function(tx, error) {
                    tx.executeSql("CREATE TABLE SessionStorage (k TEXT, v TEXT)", [], function() {});
                });
            });
        }
    };

    var empty = {
        setStorage: function(){},
        getStorage: function(){},
        removeStorage: function(){},
        clearStorage: function(){}
    };

    BrowserStore = new Class({
        initialize: function() {
            this.storage = OpenDatabase.init() || GlobalStorage.init() || LocalStorage.init() || UserData.init() || empty;
            return this;
        },
        set: function(key, vl) {
            vl && this.storage.setStorage(key, JSON.encode(vl));
            return this;
        },
        get: function(key, callback) {
            this.storage.getStorage(key, callback);
        },
        remove: function(key) {
            if (!key || ! this.storage) return false;
            key && this.storage.removeStorage(key);
            return this;
        },
        clear: function() {
            if (!this.storage) return false;
            this.storage.clearStorage();
            return this;
        }
    });
})();

