<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 *
 *
 * @package Hametuha\Model
 * @property-read string $posts
 * @property-read string $usermeta
 */
class Author extends Model
{

    protected $name = 'users';

    protected $related = ['posts', 'usermeta'];

    public function author_list_query($offset, $per_page){

    }

    /**
     * ユーザー名を更新する
     *
     * @param string $login
     * @param string $nicename
     * @param int $id
     * @return false|int
     */
    public function update_login($login, $nicename, $id){
        return $this->update([
            'user_login' => $login,
            'user_nicename' => $nicename,
        ], ['ID' => $id], ['%s', '%s'], ['%d']);
    }
}