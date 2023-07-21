/*!
 * Excerpt block
 *
 * @handle hametuha-block-container
 * @deps wp-blocks,wp-editor, wp-i18n, wp-editor, wp-data, wp-core-data, wp-element, wp-components, wp-block-editor
 * @package hametuha
 */

const { registerBlockType } = wp.blocks;
const { __, sprintf } = wp.i18n;
const { useEntityProp } = wp.coreData;
const { getCurrentPost } = wp.data.select( 'core/editor' );
const { useMemo } = wp.element;
const {
  AlignmentToolbar,
  BlockControls,
  InspectorControls,
  RichText,
  Warning,
  useBlockProps,
} = wp.blockEditor;
const { PanelBody, RangeControl, ToggleControl } = wp.components;

registerBlockType( 'hametuha/excerpt', {

  title: __( 'リード文', 'hametuha' ),

  icon: 'edit-page',

  category: 'layout',

  keywords: [ 'リード', 'lead', 'excerpt', 'layout' ],

  edit( { attributes, setAttributes, className } ){
    const post = getCurrentPost();
    const [ excerpt, setExcerpt ] = useEntityProp(
        'postType',
        post.type,
        'excerpt',
        post.id
    );
    let { max, min, placeholder } = attributes;
    switch ( post.type ) {
      case 'news':
        placeholder = __( 'ニュースの内容を要約するリード文を書いてください。', 'hametuha' );
    }
    const ok = excerpt.length < max && excerpt.length > min;
    const helperClass = ['hametuha-block-lead-status'];
    helperClass.push( ok ? 'ok' : 'ng' );
    const helperStatusText = sprintf( __( '現在%d文字: %d〜%d文字', 'hametuha' ), excerpt.length, attributes.min, attributes.max );
    return (
      <div className={ className + '-container' }>
        <RichText
            className="hametuha-block-lead-text"
            tagName={ 'p' }
            allowedFormats={ [] }
            multiline={ false }
            aria-label={ __( 'リード文', 'hametuha' ) }
            placeholder={ placeholder }
            value={ excerpt }
            onChange={ setExcerpt }
        />
        <span className={ helperClass.join( ' ' ) }>
          { ok ? (
              <span className="dashicons dashicons-yes"></span>
          ) : (
              <span className="dashicons dashicons-warning"></span>
          ) }
          {helperStatusText}
        </span>
      </div>
    )
  },

  save({className, attributes}){
    return null;
  }
} );
