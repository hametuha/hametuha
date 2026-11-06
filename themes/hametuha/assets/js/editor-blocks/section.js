/*!
 * Block Section without padding.
 *
 *
 * @handle hametuha-block-section
 * @deps wp-editor, wp-blocks, wp-element, wp-components
 */

const { registerBlockType } = wp.blocks;
const { InnerBlocks, PanelColorSettings, InspectorControls } = wp.editor;
const { Fragment } = wp.element;
const { CheckboxControl, PanelBody, ToggleControl } = wp.components;



registerBlockType( 'hametuha/section', {

  title: 'セクション',

  icon: 'excerpt-view',

  category: 'layout',

  keywords: [ 'section', 'セクション', ],

  attributes: {
    backgroundColor: {
      type: 'string',
      default: 'transparent'
    },
    withContainer: {
      type: 'boolean',
      default: true,
    }
  },

  edit({attributes, setAttributes, className}){
    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title="セクションレイアウト" className="hametuha-section-layout">
            <ToggleControl
              label="コンテナ"
              checked={ !! attributes.withContainer }
              onChange={ () => { setAttributes( { withContainer: ! attributes.withContainer } ) } }
              help="コンテナがあると、中身が中央によります。"
            />
          </PanelBody>
          <PanelColorSettings
            title="スタイル設定"
            initialOpen={false}
            colorSettings={[
              {
                value: attributes.backgroundColor,
                onChange: (value) => setAttributes({backgroundColor: value}),
                label: '背景色',
              }
            ]}
          >
          </PanelColorSettings>
        </InspectorControls>
        <div className={className} style={{backgroundColor: attributes.backgroundColor}}>
          <div className={attributes.withContainer ? 'container' : 'no-container'}>
            <InnerBlocks templateLock={false}/>
          </div>
        </div>
      </Fragment>
    )
  },

  save({className, attributes}){
    return (
      <section className={className} style={{backgroundColor: attributes.backgroundColor}}>
        <div className={attributes.withContainer ? 'container' : 'no-container'}>
          <InnerBlocks.Content />
        </div>
      </section>
    )
  }

} );
