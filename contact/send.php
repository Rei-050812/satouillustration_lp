<?php
// 出力バッファリング開始（必須）
ob_start();

// エラー報告設定
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ログ関数（エラー耐性）
function writeLog($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    // 複数の方法でログ記録を試行
    try {
        $logFile = __DIR__ . '/debug.log';
        @file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    } catch (Exception $e) {
        // ファイル書き込み失敗時はPHPエラーログに記録
    }
    
    // PHPエラーログにも記録
    @error_log("CONTACT_FORM: $message");
}

writeLog("フォーム処理開始 - バージョン2025-10-12-v2");

// 日本語メール設定
mb_language("Japanese");
mb_internal_encoding("UTF-8");

// POST検証
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeLog("非POSTリクエスト: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
    ob_end_clean();
    header('Location: index.html');
    exit;
}

// データ取得とサニタイズ
$name = isset($_POST['name']) ? trim(htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8')) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim(htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8')) : '';
$inquiry_type = isset($_POST['inquiry-type']) ? $_POST['inquiry-type'] : '';
$subject = isset($_POST['subject']) ? trim(htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8')) : '';
$message = isset($_POST['message']) ? trim(htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8')) : '';

writeLog("データ取得: name=[$name] email=[$email] type=[$inquiry_type]");

// 必須項目チェック
if (empty($name) || empty($email) || empty($inquiry_type) || empty($subject) || empty($message)) {
    writeLog("必須項目エラー");
    ob_end_clean();
    header('Location: index.html');
    exit;
}

// メールアドレス検証
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    writeLog("メール形式エラー: $email");
    ob_end_clean();
    header('Location: index.html');
    exit;
}

// 問い合わせ種別変換
$inquiry_types = [
    'general' => '一般的なご質問',
    'quote' => 'お見積もりについて',
    'process' => '制作プロセスについて',
    'schedule' => 'スケジュールについて',
    'portfolio' => '実績・ポートフォリオについて',
    'other' => 'その他'
];
$inquiry_text = isset($inquiry_types[$inquiry_type]) ? $inquiry_types[$inquiry_type] : $inquiry_type;

// 管理者宛メール本文
$admin_to = 'satoyu@satoyu-illustration.com';
$admin_subject = '【お問い合わせ】さとうゆうillustration';
$admin_body = "お問い合わせがありました。\n\n";
$admin_body .= "■ お名前\n$name\n\n";
$admin_body .= "■ メールアドレス\n$email\n\n";
$admin_body .= "■ 電話番号\n" . (!empty($phone) ? $phone : '未入力') . "\n\n";
$admin_body .= "■ お問い合わせ種別\n$inquiry_text\n\n";
$admin_body .= "■ 件名\n$subject\n\n";
$admin_body .= "■ お問い合わせ内容\n$message\n\n";
$admin_body .= "■ 送信日時\n" . date('Y年n月j日 H時i分') . "\n";

// セキュリティチェック
// ヘッダインジェクション簡易対策
foreach ([$name, $subject, $email] as $v) {
    if (preg_match('/\r|\n/', $v)) {
        writeLog('header injection suspected');
        if (ob_get_length()) { ob_end_clean(); }
        header('Location: index.html'); 
        exit;
    }
}

// ハニーポット（空であるべき）
$hp = isset($_POST['company']) ? trim($_POST['company']) : '';
if ($hp !== '') {
    writeLog('honeypot triggered');
    if (ob_get_length()) { ob_end_clean(); }
    header('Location: index.html'); 
    exit;
}

// メールヘッダー（管理者宛）
$admin_headers = [
    'From: noreply@satoyu-illustration.com',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: PHP/' . phpversion()
];

writeLog("管理者メール送信開始");

// 管理者宛メール送信
writeLog("管理者メール詳細: To=$admin_to, Subject=$admin_subject");
writeLog("管理者メールヘッダー: " . implode(" | ", $admin_headers));

$admin_sent = mail(
    $admin_to,
    mb_encode_mimeheader($admin_subject, 'UTF-8'),
    $admin_body,
    implode("\r\n", $admin_headers),
    '-f noreply@satoyu-illustration.com'
);

if(!$admin_sent){ 
    writeLog('管理者メール送信失敗: '.print_r(error_get_last(), true)); 
    // 追加のエラー情報
    writeLog('mail関数戻り値: ' . ($admin_sent ? 'true' : 'false'));
    writeLog('sendmail設定: ' . ini_get('sendmail_path'));
}

writeLog("管理者メール結果: " . ($admin_sent ? '成功' : '失敗'));

// 自動返信メール
$user_subject = '[自動返信] お問い合わせを受付いたしました';
$user_body = "$name 様\n\n";
$user_body .= "この度は、さとうゆうillustrationにお問い合わせいただき、誠にありがとうございます。\n\n";
$user_body .= "以下の内容でお問い合わせを受付いたしました。\n";
$user_body .= "3営業日以内にご返信させていただきます。\n\n";
$user_body .= "【受付内容】\n";
$user_body .= "■ お名前\n$name\n\n";
$user_body .= "■ メールアドレス\n$email\n\n";
$user_body .= "■ 電話番号\n" . (!empty($phone) ? $phone : '未入力') . "\n\n";
$user_body .= "■ お問い合わせ種別\n$inquiry_text\n\n";
$user_body .= "■ 件名\n$subject\n\n";
$user_body .= "■ お問い合わせ内容\n$message\n\n";
$user_body .= "─────────────────\n";
$user_body .= "さとうゆうillustration\n";
$user_body .= "Email: 2285satou@gmail.com\n";

$user_headers = [
    'From: noreply@satoyu-illustration.com',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: PHP/' . phpversion()
];

writeLog("自動返信メール送信開始");

// 自動返信メール送信
$user_sent = mail(
    $email,
    mb_encode_mimeheader($user_subject, 'UTF-8'),
    $user_body,
    implode("\r\n", $user_headers),
    '-f noreply@satoyu-illustration.com'
);
if(!$user_sent){ writeLog('user mail failed: '.print_r(error_get_last(), true)); }

writeLog("自動返信メール結果: " . ($user_sent ? '成功' : '失敗'));

// 送信結果をログに記録
if ($admin_sent && $user_sent) {
    writeLog("全メール送信成功 - サンクスページへリダイレクト");
} else if ($admin_sent) {
    writeLog("管理者メールのみ成功 - サンクスページへリダイレクト");
} else {
    writeLog("メール送信失敗 - サンクスページへリダイレクト");
}

// 出力バッファをクリアして、サンクスページへリダイレクト
if (ob_get_length()) { ob_end_clean(); }
header('Location: /contact/thanks.html');
exit;
?>
