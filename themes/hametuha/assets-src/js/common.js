/*!
 * 破滅派サイト全体で共通して読み込まれるファイル
 *
 * 以前は common/*.js を連結して作っていた（gulp）。いまは import でまとめる（#361）。
 * 順番は連結していたときと同じ。_hametuha.js が window.Hametuha を作るので先頭に置く。
 */

import './common/_hametuha';
import './common/form';
import './common/responsive-helper';
import './common/ui';
