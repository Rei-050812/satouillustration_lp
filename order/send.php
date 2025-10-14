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
    @error_log("ORDER_FORM: $message");
}

writeLog("制作依頼フォーム処理開始 - バージョン2025-10-12-v2");

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
$project_type = isset($_POST['project-type']) ? $_POST['project-type'] : '';
$project_title = isset($_POST['project-title']) ? trim(htmlspecialchars($_POST['project-title'], ENT_QUOTES, 'UTF-8')) : '';
$project_description = isset($_POST['project-description']) ? trim(htmlspecialchars($_POST['project-description'], ENT_QUOTES, 'UTF-8')) : '';
$budget = isset($_POST['budget']) ? trim($_POST['budget']) : '';
$deadline = isset($_POST['deadline']) ? trim(htmlspecialchars($_POST['deadline'], ENT_QUOTES, 'UTF-8')) : '';
$reference = isset($_POST['reference']) ? trim(htmlspecialchars($_POST['reference'], ENT_QUOTES, 'UTF-8')) : '';
$additional_notes = isset($_POST['additional-notes']) ? trim(htmlspecialchars($_POST['additional-notes'], ENT_QUOTES, 'UTF-8')) : '';

writeLog("データ取得: name=[$name] email=[$email] type=[$project_type]");

// 必須項目チェック
if (empty($name) || empty($email) || empty($project_type) || empty($project_title) || empty($project_description)) {
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

// 制作種別変換
$project_types = [
    'icon' => 'アイコン・ロゴ',
    'cd' => 'CD付録・ジャケット',
    'signage' => '看板・サイン',
    'web' => 'ウェブサイト用イラスト',
    'print' => '印刷物・ポスター',
    'other' => 'その他'
];
$project_type_text = isset($project_types[$project_type]) ? $project_types[$project_type] : $project_type;

// 予算選択肢変換
$budget_options = [
    'under-30000' => '3万円未満',
    '30000-50000' => '3万円〜5万円',
    '50000-100000' => '5万円〜10万円',
    '100000-200000' => '10万円〜20万円',
    'over-200000' => '20万円以上',
    'consultation' => '要相談'
];

// デバッグログ追加
writeLog("予算デバッグ: 受信した予算値 = '$budget'");
writeLog("予算デバッグ: 空かどうか = " . (empty($budget) ? 'true' : 'false'));

if (empty($budget)) {
    $budget_text = '';
    writeLog("予算デバッグ: 予算が空のため、budget_textを空に設定");
} else {
    $budget_text = isset($budget_options[$budget]) ? $budget_options[$budget] : $budget;
    writeLog("予算デバッグ: 変換後のbudget_text = '$budget_text'");
}

// 管理者宛メール本文
$admin_to = 'satoyu@satoyu-illustration.com';
$admin_subject = '【制作依頼】さとうゆうillustration';
$admin_body = "制作依頼がありました。\n\n";
$admin_body .= "■ お名前\n$name\n\n";
$admin_body .= "■ メールアドレス\n$email\n\n";
$admin_body .= "■ 電話番号\n" . (!empty($phone) ? $phone : '未入力') . "\n\n";
$admin_body .= "■ 制作種別\n$project_type_text\n\n";
$admin_body .= "■ プロジェクト名・タイトル\n$project_title\n\n";
$admin_body .= "■ 制作内容の詳細\n$project_description\n\n";
$admin_body .= "■ 希望納期\n" . (!empty($deadline) ? $deadline : '未入力') . "\n\n";
$admin_body .= "■ ご予算\n" . (!empty($budget_text) ? $budget_text : '未入力') . "\n\n";
$admin_body .= "■ 参考資料・イメージ\n" . (!empty($reference) ? $reference : '未入力') . "\n\n";
$admin_body .= "■ その他ご要望\n" . (!empty($additional_notes) ? $additional_notes : '未入力') . "\n\n";
$admin_body .= "■ 送信日時\n" . date('Y年n月j日 H時i分') . "\n";

// セキュリティチェック
// ヘッダインジェクション簡易対策
foreach ([$name, $email] as $v) {
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
$user_subject = '[自動返信] 制作依頼を受付いたしました';
$user_body = "$name 様\n\n";
$user_body .= "この度は、さとうゆうillustrationに制作依頼をいただき、誠にありがとうございます。\n\n";
$user_body .= "以下の内容で制作依頼を受付いたしました。\n";
$user_body .= "3営業日以内にお見積もりをご連絡させていただきます。\n\n";
$user_body .= "【受付内容】\n";
$user_body .= "■ お名前\n$name\n\n";
$user_body .= "■ メールアドレス\n$email\n\n";
$user_body .= "■ 電話番号\n" . (!empty($phone) ? $phone : '未入力') . "\n\n";
$user_body .= "■ 制作種別\n$project_type_text\n\n";
$user_body .= "■ プロジェクト名・タイトル\n$project_title\n\n";
$user_body .= "■ 制作内容の詳細\n$project_description\n\n";
$user_body .= "■ 希望納期\n" . (!empty($deadline) ? $deadline : '未入力') . "\n\n";
$user_body .= "■ ご予算\n" . (!empty($budget_text) ? $budget_text : '未入力') . "\n\n";
$user_body .= "■ 参考資料・イメージ\n" . (!empty($reference) ? $reference : '未入力') . "\n\n";
$user_body .= "■ その他ご要望\n" . (!empty($additional_notes) ? $additional_notes : '未入力') . "\n\n";
$user_body .= "\n─────────────────\n";
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
header('Location: /order/thanks.html');
exit;
?>
