<?php

namespace Hametuha\Admin\MetaBox;


/**
 * Series list
 *
 * @package Hametuha\Admin\MetaBox
 */
class SeriesCollaborators extends SeriesBase {

	protected $context = 'normal';

	protected $priority = 'low';

	protected $title = '執筆者一覧';

	protected $nonce_key = '_seriescollaboratorsnonce';

	protected $nonce_action = 'series-collaborators-update';

	/**
	 * Executed on admin_init
	 */
	public function adminInit() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_script' ] );
	}

	/**
	 * Enqueue script.
	 */
	public function enqueue_script() {
        $screen = get_current_screen();
        if ( 'post' !== $screen->base || 'series' !== $screen->post_type ) {
            return;
        }
        wp_enqueue_script( 'hametuha-module-collaborators-list' );
        $share_types = [];
        foreach ( $this->collaborators->share_type as $key => $value ) {
            $share_types[] = [
                'key'   => $key,
                'label' => $value,
            ];
        }
        $collaborator_types = [];
        foreach ( $this->collaborators->collaborator_type as $key => $label ) {
            $collaborator_types[] = [
                'key'   => $key,
                'label' => $label,
            ];
        }
        $post_id = (int) filter_input( INPUT_GET, 'post' );
        wp_localize_script( 'hametuha-module-collaborators-list', 'CollaboratorsList', [
            'series_id'    => $post_id,
            'share_types'  => $share_types,
            'collaborator' => $collaborator_types,
            'shareType'    => $this->collaborators->current_share_type( $post_id ),
        ] );
    }

	/**
	 * Save order and override title
	 *
	 * @param \WP_Post $post
	 */
	public function savePost( \WP_Post $post ) {
	    update_post_meta( $post->ID, '_owner_type', filter_input( INPUT_POST, 'owner_type' ) );
	}

	/**
     * Render meta box.
     *
	 * @param \WP_Post $post
	 * @param array $screen
	 */
	public function doMetaBox( \WP_Post $post, array $screen ) {
	    $collaborators = $this->collaborators->get_collaborators( $post->ID );
        ?>
        <table class="form-table">
			<tr>
				<th>責任者</th>
				<td>
                    <?php echo esc_html( get_the_author_meta( 'display_name', $post->post_author ) ) ?>
				</td>
			</tr>
            <tr>
                <th><label for="owner_type">責任者の役割</label></th>
                <td>
                    <select name="owner_type" id="owner_type">
                        <?php foreach ( $this->collaborators->owner_types as $type => $label ) : ?>
                        <option value="<?= esc_attr( $type ) ?>" <?php selected( $type, $this->collaborators->owner_type( $post->ID ) ) ?>>
                            <?= esc_html( $label ) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
		</table>
        <div id="series-collaborators"></div>
		<?php
	}
}
