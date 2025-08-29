<?php
// サーバー環境テスト用ファイル
// このファイルでサーバーの基本機能をテストできます

// 出力バッファリング開始
ob_start();

// エラー表示設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>サーバー環境テスト</h1>";
echo "<hr>";

// 1. PHP基本情報
echo "<h2>1. PHP基本情報</h2>";
echo "<strong>PHPバージョン:</strong> " . phpversion() . "<br>";
echo "<strong>サーバー時間:</strong> " . date('Y-m-d H:i:s') . "<br>";
echo "<strong>タイムゾーン:</strong> " . date_default_timezone_get() . "<br>";
echo "<hr>";

// 2. mail関数テスト
echo "<h2>2. mail関数テスト</h2>";
if (function_exists('mail')) {
    echo "<span style='color: green;'>✓ mail関数は利用可能</span><br>";
    
    // sendmail設定確認
    $sendmail_path = ini_get('sendmail_path');
    echo "<strong>sendmail_path:</strong> " . ($sendmail_path ? $sendmail_path : '未設定') . "<br>";
    
    // SMTP設定確認（Windows用）
    if (PHP_OS_FAMILY === 'Windows') {
        echo "<strong>SMTP:</strong> " . ini_get('SMTP') . "<br>";
        echo "<strong>smtp_port:</strong> " . ini_get('smtp_port') . "<br>";
    }
} else {
    echo "<span style='color: red;'>✗ mail関数は利用できません</span><br>";
}
echo "<hr>";

// 3. ファイル書き込みテスト
echo "<h2>3. ファイル書き込みテスト</h2>";
$test_file = __DIR__ . '/test_write.txt';
$test_content = "テスト書き込み: " . date('Y-m-d H:i:s') . "\n";

try {
    $write_result = @file_put_contents($test_file, $test_content, FILE_APPEND | LOCK_EX);
    if ($write_result !== false) {
        echo "<span style='color: green;'>✓ ファイル書き込み成功</span><br>";
        echo "<strong>書き込み先:</strong> $test_file<br>";
        echo "<strong>ファイルサイズ:</strong> " . filesize($test_file) . " bytes<br>";
        
        // 読み込みテスト
        $read_content = @file_get_contents($test_file);
        if ($read_content !== false) {
            echo "<span style='color: green;'>✓ ファイル読み込み成功</span><br>";
        } else {
            echo "<span style='color: red;'>✗ ファイル読み込み失敗</span><br>";
        }
    } else {
        echo "<span style='color: red;'>✗ ファイル書き込み失敗</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ ファイル書き込みエラー: " . $e->getMessage() . "</span><br>";
}
echo "<hr>";

// 4. mb_string拡張確認
echo "<h2>4. 日本語処理（mb_string）</h2>";
if (extension_loaded('mbstring')) {
    echo "<span style='color: green;'>✓ mbstring拡張は利用可能</span><br>";
    echo "<strong>内部エンコーディング:</strong> " . mb_internal_encoding() . "<br>";
    echo "<strong>言語設定:</strong> " . mb_language() . "<br>";
} else {
    echo "<span style='color: red;'>✗ mbstring拡張は利用できません</span><br>";
}
echo "<hr>";

// 5. ディレクトリ権限確認
echo "<h2>5. ディレクトリ権限</h2>";
$dirs_to_check = [
    '.' => 'カレントディレクトリ',
    './contact' => 'contactディレクトリ',
    './order' => 'orderディレクトリ'
];

foreach ($dirs_to_check as $dir => $label) {
    if (is_dir($dir)) {
        $readable = is_readable($dir) ? '読み込み可' : '読み込み不可';
        $writable = is_writable($dir) ? '書き込み可' : '書き込み不可';
        echo "<strong>$label:</strong> $readable, $writable<br>";
    } else {
        echo "<strong>$label:</strong> <span style='color: red;'>存在しません</span><br>";
    }
}
echo "<hr>";

// 6. 簡単なメール送信テスト
echo "<h2>6. テストメール送信</h2>";
if (isset($_GET['test_mail'])) {
    $test_to = 'test@example.com'; // テスト用アドレス
    $test_subject = 'サーバーテスト';
    $test_body = "これはサーバー環境テストメールです。\n送信時刻: " . date('Y-m-d H:i:s');
    $test_headers = [
        'From: test@example.com',
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    $mail_result = @mail($test_to, $test_subject, $test_body, implode("\r\n", $test_headers));
    
    if ($mail_result) {
        echo "<span style='color: green;'>✓ テストメール送信成功（実際の配信は確認してください）</span><br>";
    } else {
        echo "<span style='color: red;'>✗ テストメール送信失敗</span><br>";
    }
} else {
    echo "<a href='?test_mail=1' style='color: blue; text-decoration: underline;'>テストメール送信実行</a><br>";
    echo "<small>※実際にはメールは送信されません（test@example.com宛）</small>";
}
echo "<hr>";

// 7. エラーログ確認
echo "<h2>7. エラーログ設定</h2>";
$error_log = ini_get('error_log');
echo "<strong>エラーログファイル:</strong> " . ($error_log ? $error_log : 'システムログ') . "<br>";
echo "<strong>ログ記録:</strong> " . (ini_get('log_errors') ? '有効' : '無効') . "<br>";

// テストログ出力
@error_log("TEST: サーバー環境テスト実行 - " . date('Y-m-d H:i:s'));
echo "<span style='color: blue;'>→ テストログを出力しました</span><br>";
echo "<hr>";

// 8. 推奨設定
echo "<h2>8. 推奨設定とトラブルシューティング</h2>";
echo "<h3>メール送信がうまくいかない場合:</h3>";
echo "<ul>";
echo "<li>sendmail_pathが正しく設定されているか確認</li>";
echo "<li>サーバーのファイアウォール設定を確認</li>";
echo "<li>共用サーバーの場合、メール送信制限があるか確認</li>";
echo "<li>管理者宛メールアドレスがサーバードメインのものか確認</li>";
echo "</ul>";

echo "<h3>ログが記録されない場合:</h3>";
echo "<ul>";
echo "<li>ディレクトリの書き込み権限を確認（755または777）</li>";
echo "<li>PHPのerror_log設定を確認</li>";
echo "<li>ログファイルのパスが正しいか確認</li>";
echo "</ul>";

echo "<h3>リダイレクトがうまくいかない場合:</h3>";
echo "<ul>";
echo "<li>PHP出力前にheader()を実行</li>";
li>出力バッファリング（ob_start）を使用</li>";
echo "<li>HTMLやスペースの出力がないか確認</li>";
echo "</ul>";

// 出力バッファの内容を取得して表示
$output = ob_get_clean();
echo $output;

// クリーンアップ
if (file_exists($test_file)) {
    @unlink($test_file);
}
?>
