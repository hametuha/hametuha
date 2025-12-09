# Hametuha Dashboard 開発ガイド

このディレクトリには、hashboard 1.0系を使用したダッシュボード画面のカスタマイズが含まれています。

## 依存ライブラリ

- **hashboard** (`hametuha/hashboard` ^1.0.2) - WordPressダッシュボードフレームワーク
  - React + Bootstrap 5 ベース
  - `Hametuha\Hashboard\Pattern\Screen` を継承してカスタム画面を作成

## 重要な注意事項

### Bootstrap重複読み込みの回避

hashboardは独自のBootstrap 5をバンドルしています。テーマ側でBootstrapを読み込むスクリプト（`twitter-bootstrap`に依存するもの）をhashboardページで読み込むと、**Bootstrapが2重に読み込まれてドロップダウン等が動作しなくなります**。

対処法（`hooks/assets.php`参照）：

```php
// hashboardページではhametuha-commonを読み込まない
if ( ! get_query_var( 'hashboard' ) ) {
    wp_enqueue_script( 'hametuha-common' );
}
```

また、RestTemplateを継承したクラスで`enqueue_assets()`をオーバーライドする場合も注意：

```php
public function enqueue_assets( $page = '' ) {
    // hashboardページではBootstrap重複を避けるため読み込まない
    if ( \Hametuha\Hashboard::is_page() ) {
        return;
    }
    wp_enqueue_script( 'hametuha-your-script' );
}
```

### 親クラスのメソッド呼び出し

Screenクラスの`head()`メソッドをオーバーライドする場合、必ず`parent::head()`を呼び出してください。hashboard-helperスクリプトが読み込まれなくなります。

```php
public function head( $child = '' ) {
    parent::head( $child ); // 必須！
    wp_enqueue_script( 'your-custom-script' );
}
```

## 開発パターン

### パターン1: ダッシュボードウィジェットの追加

ダッシュボードトップ（`/dashboard/`）にウィジェットを追加する場合。

**設定ファイル:** `hooks/dashboard.php`

```php
add_filter( 'hashboard_dashboard_blocks', function ( $blocks ) {
    $blocks[] = [
        'id'    => 'my-widget',
        'title' => 'ウィジェットタイトル',
        'html'  => '<div id="my-widget-container"></div>',
        'size'  => 1, // 1 or 2（幅）
    ];
    wp_enqueue_script( 'hametuha-my-widget' );
    return $blocks;
} );
```

**Reactコンポーネント:** `assets/js/src/hashboard/my-widget.jsx`

```jsx
/*!
 * マイウィジェット
 *
 * @handle hametuha-my-widget
 * @deps wp-element, wp-api-fetch, wp-i18n, hametuha-loading-indicator
 */

const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;
const { LoadingIndicator } = wp.hametuha;

const MyWidget = () => {
    const [loading, setLoading] = useState(true);
    const [data, setData] = useState(null);

    useEffect(() => {
        wp.apiFetch({ path: '/your/api/endpoint' })
            .then(setData)
            .finally(() => setLoading(false));
    }, []);

    if (loading) return <LoadingIndicator />;
    return <div>{/* コンテンツ */}</div>;
};

// マウント
const container = document.getElementById('my-widget-container');
if (container) {
    wp.element.createRoot(container).render(<MyWidget />);
}
```

### パターン2: カスタム画面の追加（React + REST API）

`/dashboard/my-screen/` のような独立した画面を追加する場合。

**Screenクラス:** `src/Hametuha/Dashboard/MyScreen.php`

```php
<?php
namespace Hametuha\Dashboard;

use Hametuha\Hashboard\Pattern\Screen;

class MyScreen extends Screen {
    protected $icon = 'dashboard'; // Material Icons名

    public function slug() {
        return 'my-screen';
    }

    public function label() {
        return 'マイスクリーン';
    }

    public function description( $page = '' ) {
        return '画面の説明';
    }

    public function render( $page = '' ) {
        hameplate( 'templates/dashboard/my-screen' );
    }

    public function head( $child = '' ) {
        parent::head( $child ); // 必須
        wp_enqueue_script( 'hametuha-my-screen' );
    }
}
```

**登録:** `hooks/dashboard.php`

```php
add_filter( 'hashboard_screens', function ( $screens ) {
    $new_screens = [];
    foreach ( $screens as $key => $class_name ) {
        // 特定の位置に挿入
        if ( 'profile' === $key ) {
            $new_screens['my-screen'] = \Hametuha\Dashboard\MyScreen::class;
        }
        $new_screens[ $key ] = $class_name;
    }
    return $new_screens;
} );
```

**テンプレート:** `templates/dashboard/my-screen.php`

```php
<div id="my-screen-container"
     data-endpoint="/hametuha/v1/my-endpoint"
     data-user-id="<?php echo get_current_user_id(); ?>">
</div>
```

**Reactコンポーネント:** `assets/js/src/hashboard/my-screen.jsx`

```jsx
/*!
 * マイスクリーン
 *
 * @handle hametuha-my-screen
 * @deps wp-element, wp-api-fetch, wp-i18n, hametuha-loading-indicator, hametuha-pagination, hametuha-toast
 */

const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;
const { LoadingIndicator, Pagination, toast } = wp.hametuha;

const MyScreen = ({ endpoint, userId }) => {
    // 実装...
};

const container = document.getElementById('my-screen-container');
if (container) {
    const { endpoint, userId } = container.dataset;
    wp.element.createRoot(container).render(
        <MyScreen endpoint={endpoint} userId={parseInt(userId, 10)} />
    );
}
```

## 利用可能なコンポーネント

### テーマ提供（`wp.hametuha`）

| コンポーネント | ハンドル | 用途 |
|--------------|---------|------|
| `LoadingIndicator` | `hametuha-loading-indicator` | ローディング表示 |
| `Pagination` | `hametuha-pagination` | ページネーション |
| `toast` | `hametuha-toast` | トースト通知 |

### hashboard提供（`hb.components`）

| コンポーネント | ハンドル | 用途 |
|--------------|---------|------|
| `PostList` | `hb-components-post-list` | 投稿一覧表示 |
| `ListTable` | `hb-components-list-table` | テーブル表示 |

## REST APIパスの注意

`wp.apiFetch`を使用する場合、**相対パス**を指定してください。`rest_url()`で取得した完全URLを使うと、パスが二重になります。

```php
// NG: rest_url() は完全URLを返す
'endpoint' => rest_url( 'hametuha/v1/sales/history/me' ),

// OK: 相対パス
'endpoint' => '/hametuha/v1/sales/history/me',
```

テンプレートでの出力：

```php
// 相対パスなので esc_attr() を使用
data-endpoint="<?php echo esc_attr( $endpoint ); ?>"
```

## CSSについて

hashboard用のカスタムスタイルは `assets/sass/hashboard.scss` に記述します。

Bootstrap 5では以下のクラス名が変更されています：

| Bootstrap 4 | Bootstrap 5 |
|-------------|-------------|
| `form-row` | `row g-3` |
| `form-group` | 削除（不要） |
| `data-toggle` | `data-bs-toggle` |
| `data-target` | `data-bs-target` |

## ビルド

```bash
cd themes/hametuha

# JavaScript
npm run package

# CSS
npm run gulp sass
```

## 関連ファイル

- `hooks/dashboard.php` - hashboard設定・フィルター
- `hooks/assets.php` - スクリプト登録・エンキュー
- `assets/js/src/hashboard/` - hashboard用JSXファイル
- `assets/sass/hashboard.scss` - hashboard用スタイル
- `templates/dashboard/` - 画面テンプレート
