<?php
/**
 * Hameticon Font Preview
 *
 * Simple preview page for hameticon icon font
 */

// Read the icon definitions from SCSS file
$scss_file = __DIR__ . '/../assets/sass/_icon.scss';
$scss_content = file_get_contents($scss_file);

// Extract icon class names (simpler approach - just get class names)
preg_match_all('/\.icon-([a-z0-9-]+):before/i', $scss_content, $matches);

$icons = [];
foreach ($matches[1] as $name) {
    $icons[] = [
        'name' => $name,
        'class' => 'icon-' . $name,
    ];
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hameticon Font Preview</title>
    <link rel="stylesheet" href="../assets/css/app.css">
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            margin-bottom: 10px;
            color: #333;
        }

        .stats {
            color: #666;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .search-box {
            margin-bottom: 30px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }

        .icon-item {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: white;
        }

        .icon-item:hover {
            border-color: #4CAF50;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .icon-display {
            font-size: 48px;
            margin-bottom: 12px;
            color: #333;
        }

        .icon-name {
            font-size: 13px;
            color: #666;
            word-break: break-all;
            font-family: monospace;
        }

        .icon-item.hidden {
            display: none;
        }

        .copied-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s;
            pointer-events: none;
        }

        .copied-notification.show {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .icon-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 15px;
            }

            .icon-item {
                padding: 15px;
            }

            .icon-display {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Hameticon Font Preview</h1>
        <div class="stats">
            <strong><?php echo count($icons); ?></strong> icons available
        </div>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="アイコンを検索... (例: home, music, camera)">
        </div>

        <div class="icon-grid" id="iconGrid">
            <?php foreach ($icons as $icon): ?>
            <div class="icon-item" data-name="<?php echo htmlspecialchars($icon['name'], ENT_QUOTES, 'UTF-8'); ?>" data-class="<?php echo htmlspecialchars($icon['class'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="icon-display">
                    <i class="<?php echo htmlspecialchars($icon['class'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                </div>
                <div class="icon-name"><?php echo htmlspecialchars($icon['class'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="copied-notification" id="copiedNotification">
        クラス名をコピーしました！
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const iconItems = document.querySelectorAll('.icon-item');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            iconItems.forEach(item => {
                const iconName = item.dataset.name.toLowerCase();
                if (iconName.includes(searchTerm)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        });

        // Copy to clipboard on click
        const copiedNotification = document.getElementById('copiedNotification');

        iconItems.forEach(item => {
            item.addEventListener('click', function() {
                const className = this.dataset.class;

                // Copy to clipboard
                navigator.clipboard.writeText(className).then(() => {
                    // Show notification
                    copiedNotification.classList.add('show');

                    // Hide notification after 2 seconds
                    setTimeout(() => {
                        copiedNotification.classList.remove('show');
                    }, 2000);
                });
            });
        });
    </script>
</body>
</html>