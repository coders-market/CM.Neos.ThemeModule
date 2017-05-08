// https://tc39.github.io/ecma262/#sec-array.prototype.includes
if (!Array.prototype.includes) {
    Object.defineProperty(Array.prototype, 'includes', {
        value: function(searchElement, fromIndex) {

            // 1. Let O be ? ToObject(this value).
            if (this == null) {
                throw new TypeError('"this" is null or not defined');
            }

            var o = Object(this);

            // 2. Let len be ? ToLength(? Get(O, "length")).
            var len = o.length >>> 0;

            // 3. If len is 0, return false.
            if (len === 0) {
                return false;
            }

            // 4. Let n be ? ToInteger(fromIndex).
            //    (If fromIndex is undefined, this step produces the value 0.)
            var n = fromIndex | 0;

            // 5. If n ≥ 0, then
            //  a. Let k be n.
            // 6. Else n < 0,
            //  a. Let k be len + n.
            //  b. If k < 0, let k be 0.
            var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

            function sameValueZero(x, y) {
                return x === y || (typeof x === 'number' && typeof y === 'number' && isNaN(x) && isNaN(y));
            }

            // 7. Repeat, while k < len
            while (k < len) {
                // a. Let elementK be the result of ? Get(O, ! ToString(k)).
                // b. If SameValueZero(searchElement, elementK) is true, return true.
                // c. Increase k by 1.
                if (sameValueZero(o[k], searchElement)) {
                    return true;
                }
                k++;
            }

            // 8. Return false
            return false;
        }
    });
}

$(document).ready(function () {

    $('.custom-color').colorpicker();

    var myCodeMirrorScss = CodeMirror.fromTextArea(document.getElementById("code-scss"), {
        lineNumbers: true,
        matchBrackets: true,
        mode: "text/x-scss",
        gutters: ["CodeMirror-lint-markers"],
        showTrailingSpace: true,
        autoCloseBrackets: true,
        lint: {
            options: {
                rules: {
                    "no-empty-rulesets": 1,
                    "no-important": 1,
                    "no-ids": 1,
                    "hex-notation": 1,
                    "indentation": 1
                }
            }
        },
        extraKeys: {"Ctrl-Space": "autocomplete"},
        value: document.documentElement.innerHTML
    });

    var myCodeMirrorCss = CodeMirror.fromTextArea(document.getElementById("code-css"), {
        lineNumbers: true,
        matchBrackets: true,
        mode: "text/css",
        gutters: ["CodeMirror-lint-markers"],
        showTrailingSpace: true,
        autoCloseBrackets: true,
        lint: {
            options: {
                rules: {
                    "no-empty-rulesets": 1
                }
            }
        },
        extraKeys: {"Ctrl-Space": "autocomplete"},
        value: document.documentElement.innerHTML
    });
});