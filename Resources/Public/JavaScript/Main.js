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