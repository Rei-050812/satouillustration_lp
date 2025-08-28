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

// お問い合わせ内容
$inquiry_type = isset($_POST['inquiry-type']) ? $_POST['inquiry-type'] : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// 必須項目チェック
$errors = [];
if (empty($name)) $errors[] = "お名前";
if (empty($email)) $errors[] = "メールアドレス";
if (empty($inquiry_type)) $errors[] = "お問い合わせ種別";
if (empty($subject)) $errors[] = "件名";
if (empty($message)) $errors[] = "お問い合わせ内容";

if (!empty($errors)) {
    header("Location: index.html?error=" . urlencode("以下の項目が入力されていません: " . implode(", ", $errors)));
    exit();
}

// メールアドレスの形式チェック
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.html?error=" . urlencode("メールアドレスの形式が正しくありません"));
    exit();
}

// お問い合わせ種別の変換
$inquiry_types = [
    'general' => '一般的なご質問',
    'quote' => 'お見積もりについて',
    'process' => '制作プロセスについて',
    'schedule' => 'スケジュールについて',
    'portfolio' => '実績・ポートフォリオについて',
    'other' => 'その他'
];
$inquiry_type_text = isset($inquiry_types[$inquiry_type]) ? $inquiry_types[$inquiry_type] : $inquiry_type;

// 受信メール設定
$to = "r-numanou@zero-venture.com";
$mail_subject = "【さとうゆうillustration】お問い合わせ: " . $subject;

// 受信メール本文
$mail_body = "さとうゆうillustrationのウェブサイトからお問い合わせがありました。\n\n";
$mail_body .= "■お名前\n" . $name . "\n\n";
$mail_body .= "■メールアドレス\n" . $email . "\n\n";
if (!empty($phone)) {
    $mail_body .= "■電話番号\n" . $phone . "\n\n";
}
$mail_body .= "■お問い合わせ種別\n" . $inquiry_type_text . "\n\n";
$mail_body .= "■件名\n" . $subject . "\n\n";
$mail_body .= "■お問い合わせ内容\n" . $message . "\n\n";
$mail_body .= "■送信日時\n" . date("Y年m月d日 H:i:s") . "\n\n";
$mail_body .= "このメールは自動送信されています。";

// 受信メールヘッダー
$headers = "From: noreply@satouillustration.com\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// 自動返信メール設定
$auto_reply_subject = "【さとうゆうillustration】お問い合わせありがとうございます";
$auto_reply_body = $name . " 様\n\n";
$auto_reply_body .= "この度は、さとうゆうillustrationにお問い合わせいただき、誠にありがとうございます。\n\n";
$auto_reply_body .= "以下の内容でお問い合わせを受け付けいたしました。\n";
$auto_reply_body .= "内容を確認の上、3営業日以内にご連絡いたします。\n\n";
$auto_reply_body .= "【お問い合わせ内容】\n";
$auto_reply_body .= "お名前: " . $name . "\n";
$auto_reply_body .= "メールアドレス: " . $email . "\n";
if (!empty($phone)) {
    $auto_reply_body .= "電話番号: " . $phone . "\n";
}
$auto_reply_body .= "お問い合わせ種別: " . $inquiry_type_text . "\n";
$auto_reply_body .= "件名: " . $subject . "\n";
$auto_reply_body .= "お問い合わせ内容: " . $message . "\n\n";
$auto_reply_body .= "※このメールは自動送信されています。\n";
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
