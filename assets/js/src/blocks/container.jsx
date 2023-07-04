/*!
 * Container
 *
 * @handle hametuha-block-container
 * @deps wp-blocks,wp-editor
 * @package hametuha
 */

const { registerBlockType } = wp.blocks;
const { InnerBlocks } = wp.editor;


registerBlockType( 'hametuha/container', {

  title: 'コンテナ',

  icon: 'archive',

  category: 'layout',

  keywords: [ 'container', 'layout' ],

  edit({attributes, setAttributes, className}){
    return (
      <div className={ className + ' container' }>
        <InnerBlocks templateLock={false}/>
      </div>
    )
  },

  save({className, attributes}){
    return (
      <div className={className + ' container'}>
        <InnerBlocks.Content />
      </div>
    )
  }
} );
