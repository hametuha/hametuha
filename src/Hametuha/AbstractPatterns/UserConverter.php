<?php

namespace Hametuha\AbstractPatterns;


/**
 * Convert user to REST ready data format.
 *
 * @package hametuha
 */
trait UserConverter {

	/**
	 * Return array
	 *
	 * @param \WP_User $user
	 * @return array
	 */
	protected function to_array( $user ) {
		return [
			'id'     => $user->ID,
			'name'   => $user->display_name,
			'avatar' => get_avatar_url( $user->ID ),
			'url'    => hametuha_author_url( $user->ID ),
			'ratio'  => 0 <= $user->ratio ? 100 * $user->ratio : -1,
			'assigned' => $user->assigned,
			'type'     => $user->collaboration_type,
			'label'    => $this->collaborators->collaborator_type[ $user->collaboration_type ],
		];
	}
}
