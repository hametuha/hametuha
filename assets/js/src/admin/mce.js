/**
 * Description
 */

/*global tinymce: true*/

tinymce.PluginManager.add('hametuha', function(editor, url) {

    /**
     * Apply CSS to TinyMCE editor
     *
     * @param {Document} d
     * @param {String} css
     */
    function applyStyle(d, css){
        var style;
        if( !(style = d.getElementById('hametuha-mce')) ){
            style = d.createElement('style');
            style.type = 'text/css';
            style.id = 'hametuha-mce';
            d.getElementsByTagName('head')[0].appendChild(style);
        }
        console.log(style);
        style.innerHTML = css;
    }

    /**
     * Detect if this element requires indent
     *
     * @param {Object} node
     * @return {Boolean}
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
        var dom = editor.dom,
            selectors = [],
            nodes = dom.doc.body.childNodes;
        for( var i = 0, l = nodes.length; i < l; i++ ){
            switch( nodes[i].nodeName ){
                case 'P':
                    if( !needIndent(nodes[i]) ){
                        selectors.push('body > p:nth-child(' + (i + 1) + ')')
                    }
                    break;
                case 'BLOCKQUOTE':
                    (function(node, index){
                        var children = node.childNodes;
                        for( var j = 0, k = children.length; j < k; j++ ){
                            if( 'P' === children[j].nodeName && !needIndent(children[j]) ){
                                selectors.push('body > blockquote:nth-child(' + (index + 1) + ') > p:nth-child(' + ( j + 1 ) + ')');
                            }
                        }
                    })(nodes[i], i);
                    break;
                default:
                    // Do nothing
                    break;
            }
        }
        if( selectors.length ){
            applyStyle(dom.doc, selectors.join(',') + '{text-indent: 0;}')
        }
    }

    // Add event listener
    editor.on('init', setP);
    editor.on('change', setP);

});

