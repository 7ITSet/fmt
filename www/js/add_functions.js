Math.decimalPlaces = function(num, p){return Math.round(num*(Math.pow(10,p)))/Math.pow(10,p)};
function var_dump () {
     var output = '', pad_char = ' ', pad_val = 4, lgth = 0, i = 0, d = this.window.document;
     var getFuncName = function (fn) {
         var name = (/\W*function\s+([\w\$]+)\s*\(/).exec(fn);
         if (!name) {
             return '(Anonymous)';
         }
         return name[1];
     };
     var repeat_char = function (len, pad_char) {
         var str = '';
         for (var i=0; i < len; i++) {
             str += pad_char;
         }
         return str;
     };
     var getScalarVal = function (val) {
         var ret = '';
         if (val === null) {
              ret = 'NULL';
          } else if (typeof val === 'boolean') {
              ret = 'bool(' + val + ')';
          } else if (typeof val === 'string') {
              ret = 'string(' + val.length + ') "' + val + '"';
          } else if (typeof val === 'number') {
              if (parseFloat(val) == parseInt(val, 10)) {
                  ret = 'int(' + val + ')';
              } else {
                 ret = 'float(' + val + ')';
              }
          } else if (val === undefined) {
              ret = 'UNDEFINED'; // Not PHP behavior, but neither is undefined as value
          }  else if (typeof val === 'function') {
              ret = 'FUNCTION'; // Not PHP behavior, but neither is function as value
              ret = val.toString().split("\n");
              txt = '';
              for(var j in ret) {
                  txt += (j !=0 ? thick_pad : '') + ret[j] + "\n";
              }
              ret = txt;
         } else if (val instanceof Date) {
              val = val.toString();
              ret = 'string('+val.length+') "' + val + '"'
          }
          else if(val.nodeName) {
              ret = 'HTMLElement("' + val.nodeName.toLowerCase() + '")';
          }
          return ret;
      };
      var formatArray = function (obj, cur_depth, pad_val, pad_char) {
          var someProp = '';
          if (cur_depth > 0) {
             cur_depth++;
         }
         base_pad = repeat_char(pad_val * (cur_depth - 1), pad_char);
         thick_pad = repeat_char(pad_val * (cur_depth + 1), pad_char);
         var str = '';
         var val = '';
         if (typeof obj === 'object' && obj !== null) {
             if (obj.constructor && getFuncName(obj.constructor) === 'PHPJS_Resource') {
                 return obj.var_dump();
             }
             lgth = 0;
             for (someProp in obj) {
                 lgth++;
             }
             str += "array(" + lgth + ") {\n";
             for (var key in obj) {
                 if (typeof obj[key] === 'object' && obj[key] !== null && !(obj[key] instanceof Date) && !obj[key].nodeName) {
                     str += thick_pad + "["+key+"] =>\n" + thick_pad+formatArray(obj[key], cur_depth+1, pad_val, pad_char);
                 } else {
                     val = getScalarVal(obj[key]);
                     str += thick_pad + "["+key+"] =>\n" + thick_pad + val + "\n";
                 }
             }
             str += base_pad + "}\n";
         } else {
             str = getScalarVal(obj);
         }
         return str;
     };
     output = formatArray(arguments[0], 0, pad_val, pad_char);
     for ( i=1; i < arguments.length; i++ ) {
         output += '\n' + formatArray(arguments[i], 0, pad_val, pad_char);
     }
     return output;
}

function in_array(needle, haystack, strict) {

	var found = false, key, strict = !!strict;

	for (key in haystack) {
		if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
			found = true;
			break;
		}
	}

	return found;
}

/*!
 * Lightweight URL manipulation with JavaScript
 * This library is independent of any other libraries and has pretty simple
 * interface and lightweight code-base.
 * Some ideas of query string parsing had been taken from Jan Wolter
 * @see http://unixpapa.com/js/querystring.html
 *
 * @license MIT
 * @author Mykhailo Stadnyk <mikhus@gmail.com>
 */
(function (ns) {
    'use strict';

    // configure given url options
    function urlConfig (url) {
        var config = {
            path: true,
            query: true,
            hash: true
        };

        if (!url) {
            return config;
        }

        if (/^[a-z]+:/.test(url)) {
            config.protocol = true;
            config.host = true;

            if (/[-a-z0-9]+(\.[-a-z0-9])*:\d+/i.test(url)) {
                config.port = true;
            }

            if (/\/\/(.*?)(?::(.*?))?@/.test(url)) {
                config.user = true;
                config.pass = true;
            }
        }

        return config;
    }

    var isNode = typeof window === 'undefined' &&
        typeof global !== 'undefined' &&
        typeof require === 'function';

    // Trick to bypass Webpack's require at compile time
    var nodeRequire = isNode ? ns['require'] : null;

    // mapping between what we want and <a> element properties
    var map = {
        protocol: 'protocol',
        host: 'hostname',
        port: 'port',
        path: 'pathname',
        query: 'search',
        hash: 'hash'
    };

    // jscs: disable
    /**
     * default ports as defined by http://url.spec.whatwg.org/#default-port
     * We need them to fix IE behavior, @see https://github.com/Mikhus/jsurl/issues/2
     */
    // jscs: enable
    var defaultPorts = {
        ftp: 21,
        gopher: 70,
        http: 80,
        https: 443,
        ws: 80,
        wss: 443
    };

    function parse (self, url, absolutize) {
        var link, i, auth;
        var currUrl = isNode ? ('file://' +
            (process.platform.match(/^win/i) ? '/' : '') +
            nodeRequire('fs').realpathSync('.')
        ) : document.location.href;

        if (!url) {
            url = currUrl;
        }

        if (isNode) {
            link = nodeRequire('url').parse(url);
        }

        else {
            link = document.createElement('a');
            link.href = url;
        }

        var config = urlConfig(url);

        auth = url.match(/\/\/(.*?)(?::(.*?))?@/) || [];

        for (i in map) {
            if (config[i]) {
                self[i] = link[map[i]] || '';
            }

            else {
                self[i] = '';
            }
        }

        // fix-up some parts
        self.protocol = self.protocol.replace(/:$/, '');
        self.query = self.query.replace(/^\?/, '');
        self.hash = decode(self.hash.replace(/^#/, ''));
        self.user = decode(auth[1] || '');
        self.pass = decode(auth[2] || '');
        /* jshint ignore:start */
        self.port = (
            // loosely compare because port can be a string
            defaultPorts[self.protocol] == self.port || self.port == 0
        ) ? '' : self.port; // IE fix, Android browser fix
        /* jshint ignore:end */

        if (!config.protocol && /[^/#?]/.test(url.charAt(0))) {
            self.path = url.split('?')[0].split('#')[0];
        }

        if (!config.protocol && absolutize) {
            // is IE and path is relative
            var base = new Url(currUrl.match(/(.*\/)/)[0]);
            var basePath = base.path.split('/');
            var selfPath = self.path.split('/');
            var props = ['protocol', 'user', 'pass', 'host', 'port'];
            var s = props.length;

            basePath.pop();

            for (i = 0; i < s; i++) {
                self[props[i]] = base[props[i]];
            }

            while (selfPath[0] === '..') { // skip all "../
                basePath.pop();
                selfPath.shift();
            }

            self.path =
                (url.charAt(0) !== '/' ? basePath.join('/') : '') +
                '/' + selfPath.join('/')
            ;
        }

        self.path = self.path.replace(/^\/{2,}/, '/');

        self.paths((self.path.charAt(0) === '/' ?
            self.path.slice(1) : self.path).split('/')
        );

        self.query = new QueryString(self.query);
    }

    function encode (s) {
        return encodeURIComponent(s).replace(/'/g, '%27');
    }

    function decode (s) {
        s = s.replace(/\+/g, ' ');

        s = s.replace(/%([ef][0-9a-f])%([89ab][0-9a-f])%([89ab][0-9a-f])/gi,
            function (code, hex1, hex2, hex3) {
                var n1 = parseInt(hex1, 16) - 0xE0;
                var n2 = parseInt(hex2, 16) - 0x80;

                if (n1 === 0 && n2 < 32) {
                    return code;
                }

                var n3 = parseInt(hex3, 16) - 0x80;
                var n = (n1 << 12) + (n2 << 6) + n3;

                if (n > 0xFFFF) {
                    return code;
                }

                return String.fromCharCode(n);
            }
        );

        s = s.replace(/%([cd][0-9a-f])%([89ab][0-9a-f])/gi,
            function (code, hex1, hex2) {
                var n1 = parseInt(hex1, 16) - 0xC0;

                if (n1 < 2) {
                    return code;
                }

                var n2 = parseInt(hex2, 16) - 0x80;

                return String.fromCharCode((n1 << 6) + n2);
            }
        );

        return s.replace(/%([0-7][0-9a-f])/gi,
            function (code, hex) {
                return String.fromCharCode(parseInt(hex, 16));
            }
        );
    }

    /**
     * Class QueryString
     *
     * @param {string} qs - string representation of QueryString
     * @constructor
     */
    function QueryString (qs) {
        var re = /([^=&]+)(=([^&]*))?/g;
        var match;

        while ((match = re.exec(qs))) {
            var key = decodeURIComponent(match[1].replace(/\+/g, ' '))
            var value = match[3] ? decode(match[3]) : '';

            if (!(this[key] === undefined || this[key] === null)) {
                if (!(this[key] instanceof Array)) {
                    this[key] = [this[key]];
                }

                this[key].push(value);
            }

            else {
                this[key] = value;
            }
        }
    }

    /**
     * Converts QueryString object back to string representation
     *
     * @returns {string}
     */
    QueryString.prototype.toString = function () {
        var s = '';
        var e = encode;
        var i, ii;

        for (i in this) {
            if (this[i] instanceof Function || this[i] === null) {
                continue;
            }

            if (this[i] instanceof Array) {
                var len = this[i].length;
                if (len) {
                    for (ii = 0; ii < len; ii++) {
                        s += s ? '&' : '';
                        s += e(i) +((i.indexOf('[]',0)!=-1)?'':'[]')+'=' + e(this[i][ii]);
                    }
                }

                else {
                    // parameter is an empty array, so treat as
                    // an empty argument
                    s += (s ? '&' : '') + e(i) + '=';
                }
            }

            else {
                s += s ? '&' : '';
                s += e(i) + '=' + e(this[i]);
            }
        }

        return s;
    };

    /**
     * Class Url
     *
     * @param {string} [url] - string URL representation
     * @param {boolean} [noTransform] - do not transform to absolute URL
     * @constructor
     */
    function Url (url, noTransform) {
        parse(this, url, !noTransform);
    }

    /**
     * Clears QueryString, making it contain no params at all
     *
     * @returns {Url}
     */
    Url.prototype.clearQuery = function () {
        for (var key in this.query) {
            if (!(this.query[key] instanceof Function)) {
                delete this.query[key];
            }
        }

        return this;
    };

    /**
     * Returns total number of parameters in QueryString
     *
     * @returns {number}
     */
    Url.prototype.queryLength = function () {
        var count = 0;
        var key;

        for (key in this) {
            if (!(this[key] instanceof Function)) {
                count++;
            }
        }

        return count;
    };

    /**
     * Returns true if QueryString contains no parameters, false otherwise
     *
     * @returns {boolean}
     */
    Url.prototype.isEmptyQuery = function () {
        return this.queryLength() === 0;
    };

    /**
     *
     * @param {Array} [paths] - an array pf path parts (if given will modify
     *                          Url.path property
     * @returns {Array} - an array representation of the Url.path property
     */
    Url.prototype.paths = function (paths) {
        var prefix = '';
        var i = 0;
        var s;

        if (paths && paths.length && paths + '' !== paths) {
            if (this.isAbsolute()) {
                prefix = '/';
            }

            for (s = paths.length; i < s; i++) {
                paths[i] = !i && paths[i].match(/^\w:$/) ? paths[i] :
                    encode(paths[i]);
            }

            this.path = prefix + paths.join('/');
        }

        paths = (this.path.charAt(0) === '/' ?
            this.path.slice(1) : this.path).split('/');

        for (i = 0, s = paths.length; i < s; i++) {
            paths[i] = decode(paths[i]);
        }

        return paths;
    };

    /**
     * Performs URL-specific encoding of the given string
     *
     * @method Url#encode
     * @param {string} s - string to encode
     * @returns {string}
     */
    Url.prototype.encode = encode;

    /**
     * Performs URL-specific decoding of the given encoded string
     *
     * @method Url#decode
     * @param {string} s - string to decode
     * @returns {string}
     */
    Url.prototype.decode = decode;

    /**
     * Checks if current URL is an absolute resource locator (globally absolute
     * or absolute path to current server)
     *
     * @returns {boolean}
     */
    Url.prototype.isAbsolute = function () {
        return this.protocol || this.path.charAt(0) === '/';
    };

    /**
     * Returns string representation of current Url object
     *
     * @returns {string}
     */
    Url.prototype.toString = function () {
        return (
            (this.protocol && (this.protocol + '://')) +
            (this.user && (
            encode(this.user) + (this.pass && (':' + encode(this.pass))
            ) + '@')) +
            (this.host && this.host) +
            (this.port && (':' + this.port)) +
            (this.path && this.path) +
            (this.query.toString() && ('?' + this.query)) +
            (this.hash && ('#' + encode(this.hash)))
        );
    };

    ns[ns.exports ? 'exports' : 'Url'] = Url;
}(typeof module !== 'undefined' && module.exports ? module : window));


// возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
  var matches = document.cookie.match(new RegExp(
	"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}
function setCookie(name, value, options) {
  options = options || {};

  var expires = options.expires;

  if (typeof expires == "number" && expires) {
	var d = new Date();
	d.setTime(d.getTime() + expires * 1000);
	expires = options.expires = d;
  }
  if (expires && expires.toUTCString) {
	options.expires = expires.toUTCString();
  }

  value = encodeURIComponent(value);

  var updatedCookie = name + "=" + value;

  for (var propName in options) {
	updatedCookie += "; " + propName;
	var propValue = options[propName];
	if (propValue !== true) {
	  updatedCookie += "=" + propValue;
	}
  }

  document.cookie = updatedCookie;
}
$.fn.setCursorPosition = function (pos) {
	this.each(function (index, elem) {
		if (elem.setSelectionRange) {
			elem.setSelectionRange(pos, pos);
		} else if (elem.createTextRange) {
			var range = elem.createTextRange();
			range.collapse(true);
			range.moveEnd('character', pos);
			range.moveStart('character', pos);
			range.select();
		}
	});
	return this;
};

function popup_show(id,func){
	//показ инфоблоков поверх попапов
	if(id=='#popup_infobox'&&$('#blocked').hasClass('dark')){
		$(id).css('z-index',2000);
		$($('#blocked')).css('z-index',1500);
		$($('#popups > div:not(#popup_infobox)')).css('box-shadow','rgba(60,60,60,.5) 0 0 25px');
		$(id).fadeIn(200,function(){
			});
		return false;
	}
	//флаг для выполнения функции после показа попапа только один раз, а не на всех элементах с событием fadeIn
	var func_complete=false;
	$('#blocked').addClass('dark');
	$('#blocked,#popups').fadeIn(200);
	$(id).fadeIn(200,function(){
		if(!func_complete&&func) func();
		func_complete=true;
	});
	return false;
}
function popup_hide(func){
	var func_complete=false;
	$('#blocked').removeClass('dark');
	$('#popups,.popup').fadeOut(200,function(){
		//func?func():null;
	});
	$('#blocked').fadeOut(200,function(){
		if(!func_complete&&func) func();
		func_complete=true;
	});
	return false;
}
//инфо-лайтбоксы
infobox_show=function(header,message,type){
	$('#popup_infobox .popup_header p').text(header);
	if(type=='ok'){
		$('#popup_infobox p.popup-error').hide();
		$('#popup_infobox p.popup-success').show().html(message);
	}
	if(type=='error'){
		$('#popup_infobox p.popup-success').hide();
		$('#popup_infobox p.popup-error').show().html(message);
	}
	popup_show('#popup_infobox');
}
loadCatalog=function(){
	$.get(
		'/ajax/menu_catalog.php',
		{},
		function(data){
			$('.nav_wrapper').html(data);
			$("#menu_id_2000000000 span:first").text("КАТАЛОГ ТОВАРОВ");
		}
	);
}
JSON.stringify = JSON.stringify || function (obj) {
    var t = typeof (obj);
    if (t != "object" || obj === null) {
        // simple data type
        if (t == "string") obj = '"'+obj+'"';
        return String(obj);
    }
    else {
        // recurse array or object
        var n, v, json = [], arr = (obj && obj.constructor == Array);
        for (n in obj) {
            v = obj[n]; t = typeof(v);
            if (t == "string") v = '"'+v+'"';
            else if (t == "object" && v !== null) v = JSON.stringify(v);
            json.push((arr ? "" : '"' + n + '":') + String(v));
        }
        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
};
Array.prototype.clean = function(deleteValue) {
  for (var i = 0; i < this.length; i++) {
    if (this[i] == deleteValue) {
      this.splice(i, 1);
      i--;
    }
  }
  return this;
};
