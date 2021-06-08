/*!
 * TinuMCE helper
 */

/*global tinymce: true*/

tinymce.PluginManager.add('hametuha', function(editor, url) {

    "use strict";

    /**
     * Apply CSS to TinyMCE editor
     *
     * @param {Document} d
     * @param {string} css
     */
    function applyStyle(d, css){
        let style;
        if( !(style = d.getElementById('hametuha-mce')) ){
            style = d.createElement('style');
            style.type = 'text/css';
            style.id = 'hametuha-mce';
            d.getElementsByTagName('head')[0].appendChild(style);
        }
        style.innerHTML = css;
    }

    /**
     * Detect if this element requires indent
     *
     * @param {Object} node
     * @return {boolean}
     */
    function needIndent(node){
        return !(/^[ 　【】《〔〝『「（”"'’\(\)]/.test(node.textContent));
    }

    /**
     * Set auto indent
     *
     * @param {Object} e
     */
    function setP(e){
        const dom = editor.dom,
            selectors = [],
            nodes = dom.doc.body.childNodes;
        jQuery.each(nodes, function(i, node){
            switch( node.nodeName ){
                case 'P':
                    if( !needIndent(node) ){
                        selectors.push('body > p:nth-child(' + (i + 1) + ')');
                    }
                    break;
                case 'BLOCKQUOTE':
                    var children = node.childNodes;
                    for (let j = 0, k = children.length; j < k; j++) {
                        if ('P' === children[j].nodeName && !needIndent(children[j])) {
                            selectors.push('body > blockquote:nth-child(' + (i + 1) + ') > p:nth-child(' + ( j + 1 ) + ')');
                        }
                    }
                    break;
                default:
                    // Do nothing
                    break;
            }

        });
        if( selectors.length ){
            applyStyle(dom.doc, selectors.join(',') + '{text-indent: 0;}');
        }
    }

    // Add event listener
    editor.on('init', setP);
    editor.on('change', setP);

});

