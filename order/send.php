<?php
// 文字エンコーディングを設定
mb_internal_encoding("UTF-8");

// POSTデータの取得と検証
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.html");
    exit();
}

// 基本情報
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

// 制作内容
$project_type = isset($_POST['project-type']) ? $_POST['project-type'] : '';
$project_title = isset($_POST['project-title']) ? trim($_POST['project-title']) : '';
$project_description = isset($_POST['project-description']) ? trim($_POST['project-description']) : '';
$budget = isset($_POST['budget']) ? $_POST['budget'] : '';
$deadline = isset($_POST['deadline']) ? trim($_POST['deadline']) : '';
$additional_info = isset($_POST['additional-info']) ? trim($_POST['additional-info']) : '';

// 必須項目チェック
$errors = [];
if (empty($name)) $errors[] = "お名前";
if (empty($email)) $errors[] = "メールアドレス";
if (empty($project_type)) $errors[] = "制作種別";
if (empty($project_title)) $errors[] = "プロジェクト名・タイトル";
if (empty($project_description)) $errors[] = "制作内容の詳細";

if (!empty($errors)) {
    header("Location: index.html?error=" . urlencode("以下の項目が入力されていません: " . implode(", ", $errors)));
    exit();
}

// メールアドレスの形式チェック
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.html?error=" . urlencode("メールアドレスの形式が正しくありません"));
    exit();
}

// 制作種別の変換
$project_types = [
    'icon' => 'アイコン・ロゴ',
    'cd' => 'CD付録・ジャケット',
    'signage' => '看板・サイン',
    'web' => 'ウェブサイト用イラスト',
    'print' => '印刷物・ポスター',
    'other' => 'その他'
];
$project_type_text = isset($project_types[$project_type]) ? $project_types[$project_type] : $project_type;

// 予算の変換
$budget_options = [
    'under-50k' => '5万円未満',
    '50k-100k' => '5万円〜10万円',
    '100k-200k' => '10万円〜20万円',
    'over-200k' => '20万円以上',
    'discuss' => '相談したい'
];
$budget_text = isset($budget_options[$budget]) ? $budget_options[$budget] : $budget;

// 受信メール設定
$to = "r-numanou@zero-venture.com";
$mail_subject = "【さとうゆうillustration】制作依頼: " . $project_title;

// 受信メール本文
$mail_body = "さとうゆうillustrationのウェブサイトから制作依頼がありました。\n\n";
$mail_body .= "■お名前\n" . $name . "\n\n";
$mail_body .= "■メールアドレス\n" . $email . "\n\n";
if (!empty($phone)) {
    $mail_body .= "■電話番号\n" . $phone . "\n\n";
}
$mail_body .= "■制作種別\n" . $project_type_text . "\n\n";
$mail_body .= "■プロジェクト名・タイトル\n" . $project_title . "\n\n";
$mail_body .= "■制作内容の詳細\n" . $project_description . "\n\n";
if (!empty($budget)) {
    $mail_body .= "■予算\n" . $budget_text . "\n\n";
}
if (!empty($deadline)) {
    $mail_body .= "■希望納期\n" . $deadline . "\n\n";
}
if (!empty($additional_info)) {
    $mail_body .= "■その他・補足事項\n" . $additional_info . "\n\n";
}
$mail_body .= "■送信日時\n" . date("Y年m月d日 H:i:s") . "\n\n";
$mail_body .= "このメールは自動送信されています。";

// 受信メールヘッダー
$headers = "From: noreply@satouillustration.com\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// 自動返信メール設定
$auto_reply_subject = "【さとうゆうillustration】制作依頼ありがとうございます";
$auto_reply_body = $name . " 様\n\n";
$auto_reply_body .= "この度は、さとうゆうillustrationに制作依頼をいただき、誠にありがとうございます。\n\n";
$auto_reply_body .= "以下の内容で制作依頼を受け付けいたしました。\n";
$auto_reply_body .= "内容を確認の上、3営業日以内にお見積もりをご連絡いたします。\n\n";
$auto_reply_body .= "【制作依頼内容】\n";
$auto_reply_body .= "お名前: " . $name . "\n";
$auto_reply_body .= "メールアドレス: " . $email . "\n";
if (!empty($phone)) {
    $auto_reply_body .= "電話番号: " . $phone . "\n";
}
$auto_reply_body .= "制作種別: " . $project_type_text . "\n";
$auto_reply_body .= "プロジェクト名: " . $project_title . "\n";
$auto_reply_body .= "制作内容: " . $project_description . "\n";
if (!empty($budget)) {
    $auto_reply_body .= "予算: " . $budget_text . "\n";
}
if (!empty($deadline)) {
    $auto_reply_body .= "希望納期: " . $deadline . "\n";
}
if (!empty($additional_info)) {
    $auto_reply_body .= "その他: " . $additional_info . "\n";
}
$auto_reply_body .= "\n※このメールは自動送信されています。\n";
$auto_reply_body .= "※ご返信いただいても対応できませんので、ご了承ください。\n\n";
$auto_reply_body .= "─────────────────────\n";
$auto_reply_body .= "さとうゆうillustration\n";
$auto_reply_body .= "E-mail: r-numanou@zero-venture.com\n";
$auto_reply_body .= "Website: https://satouillustration.com\n";
$auto_reply_body .= "─────────────────────";

// 自動返信メールヘッダー
$auto_reply_headers = "From: noreply@satouillustration.com\r\n";
$auto_reply_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// メール送信
$mail_sent = false;
$auto_reply_sent = false;

try {
    // 受信メール送信
    $mail_sent = mb_send_mail($to, $mail_subject, $mail_body, $headers);
    
    // 自動返信メール送信
    $auto_reply_sent = mb_send_mail($email, $auto_reply_subject, $auto_reply_body, $auto_reply_headers);
    
    if ($mail_sent && $auto_reply_sent) {
        // 成功時はサンクスページにリダイレクト
        header("Location: thanks.html");
        exit();
    } else {
        throw new Exception("メール送信に失敗しました");
    }
} catch (Exception $e) {
    // エラー時は元のページにリダイレクト
    header("Location: index.html?error=" . urlencode("メール送信に失敗しました。しばらく時間をおいて再度お試しください。"));
    exit();
}
?>
