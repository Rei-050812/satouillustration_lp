<?php
// 診断用ファイル
echo "=== サーバー診断 ===\n";
echo "現在時刻: " . date('Y-m-d H:i:s') . "\n";
echo "PHPバージョン: " . phpversion() . "\n";
echo "\n";

// send.phpの内容を確認
$sendPhpPath = __DIR__ . '/send.php';
echo "send.phpのパス: $sendPhpPath\n";
echo "send.phpの存在: " . (file_exists($sendPhpPath) ? 'あり' : 'なし') . "\n";

if (file_exists($sendPhpPath)) {
    echo "send.phpの更新日時: " . date('Y-m-d H:i:s', filemtime($sendPhpPath)) . "\n";
    echo "send.phpのサイズ: " . filesize($sendPhpPath) . " bytes\n";

    // 最初の50行を表示
    $lines = file($sendPhpPath);
    echo "\n=== send.php 最初の50行 ===\n";
    for ($i = 0; $i < min(50, count($lines)); $i++) {
        echo ($i + 1) . ": " . htmlspecialchars($lines[$i]) . "\n";
    }

    // Fromヘッダーを含む行を検索
    echo "\n=== 'From:' を含む行 ===\n";
    foreach ($lines as $num => $line) {
        if (stripos($line, 'From:') !== false || stripos($line, "'From:") !== false) {
            echo ($num + 1) . ": " . htmlspecialchars($line) . "\n";
        }
    }
}

echo "\n=== OPcache情報 ===\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    echo "OPcache有効: " . ($status['opcache_enabled'] ? 'はい' : 'いいえ') . "\n";
    echo "キャッシュフル: " . ($status['cache_full'] ? 'はい' : 'いいえ') . "\n";
} else {
    echo "OPcacheが利用できません\n";
}
?>
