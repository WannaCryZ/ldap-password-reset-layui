<?php
include_once './extend/PHPMailer.php';
include_once './config.php';
//发送重置链接邮件
function sendMail($to, $recipinetname, $username, $code, &$result)
{
    if ($to == null || $recipinetname == null || $username == null) {
        echo "信息校验失败";
        exit;
    }
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->SMTPDebug = 0; // Enable verbose debug output
        $mail->isSMTP(); // Send using SMTP
        $mail->Host = MAIL_HOST; // Set the SMTP server to send through
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = MAIL_USER; // SMTP username
        $mail->Password = MAIL_PASS; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port = MAIL_PORT; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom(MAIL_USER, 'IT支持');  // 设置发件人
        $mail->addAddress($to, $recipinetname); // Add a recipient
        // Content
        $mail->isHTML(true); // Set email format to HTML       // 设置邮件内容为HTML格式
        //生成重置密码的链接
        $reset_password_link = RESET_LINK . 'reset.html?username=' . $username . '&code=' . $code; // 替换为你的重置密码页面链接
        $mail->Subject = '【测试环境】LDAP账号重置密码'; // 设置邮件主题

        $mail_content = '您好，' . $recipinetname . '：<br><br>您申请了测试环境LDAP账号密码重置，<br><br>请点击链接重置密码：' . '<a href="' . $reset_password_link . '">[重置密码链接]</a>（链接有效期24小时）' .
            '<br><br>如果您无法点击链接，请将以下地址复制到浏览器地址栏中：<br><br>' . $reset_password_link .
            '<br><br>如果您没有申请重置密码，请忽略此邮件。<br><br>
        如果您在登录或使用过程中遇到任何问题，请随时联系运维。<br><br>
        该LDAP账号用于测试环境系统访问，如jenkins、yapi等。<br><br>
			获取更多信息：<br><br>- 运维系统导航：<a href="https://ops.example.com/">https://ops.example.com/</a>（需公司内网访问）<br><br>
            <br>'; // 设置邮件正文
        $mail_sign = '<div class="qqmail_sign" id="wemailsigcontent" signid="200">
            <div class="rich_custom_signature" style="position: relative;">
                <p><b>IT支持组 </b><b>| </b><b>狼人的公司</b><b>-部门</b></p>
                <div>重要提示：本邮件及附件具保密性质，可能包含商业秘密及根据法律享有特权或不得披露的信息。</div>
                <div>如果您意外收到此邮件，特此提醒您本邮件的机密性，请立即回复邮件通知我们并从您的系统中删除本邮件及附件。</div>
                <div>如果您不是本邮件应当的收件人，请注意不可利用、复制本邮件及其附件内容或向他人披露该等内容。</div>
            </div>
            <br>
            <div style="font-size: 11pt;color: #000;line-height: 1.43;"><br>
            </div>
            <div style="font-size: 11pt;color: #000;line-height: 1.43;"><br>
            </div>
        </div>';
        $mail->Body = $mail_content . '<br>' . $mail_sign; // 设置邮件正文
        //$mail->AltBody = strip_tags($body); // 设置邮件正文（纯文本）
        $mail->send(); // 发送邮件
        $result = '邮件发送成功！！'; // 发送成功
    } catch (Exception $e) {
        $result = "邮件发送失败. 原因: {$mail->ErrorInfo}"; // 发送失败
    }
}
//发送密码修改通知邮件
function sendMailNotice($to, $recipinetname, $username, &$result)
{
    if ($to == null || $recipinetname == null || $username == null) {
        echo "信息校验失败";
        exit;
    }
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->SMTPDebug = 0; // Enable verbose debug output
        $mail->isSMTP(); // Send using SMTP
        $mail->Host = MAIL_HOST; // Set the SMTP server to send through
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = MAIL_USER; // SMTP username
        $mail->Password = MAIL_PASS; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port = MAIL_PORT; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom(MAIL_USER, 'IT支持');  // 设置发件人
        $mail->addAddress($to, $recipinetname); // Add a recipient
        // Content
        $mail->isHTML(true); // Set email format to HTML       // 设置邮件内容为HTML格式
        //生成重置密码的链接

        $mail->Subject = '【测试环境】LDAP账号密码修改通知'; // 设置邮件主题
        $mail_content = '您好，' . $recipinetname . '：<br><br>您已成功修改了测试环境LDAP账号（' . $username . '）的密码，请牢记并保管好您的新密码。' .
            '<br><br>如果不是您本人操作的修改，请立即联系运维。<br><br><br><br>
            该LDAP账号用于测试环境系统访问，如jenkins、yapi等。<br><br>
			获取更多信息：<br><br>- 运维系统导航：<a href="https://ops.example.com/">https://ops.example.com/</a>（需公司内网访问）<br><br>
            <br>'; // 设置邮件正文
        $mail_sign = '<div class="qqmail_sign" id="wemailsigcontent" signid="200">
            <div class="rich_custom_signature" style="position: relative;">
                <p><b>IT支持组 </b><b>| </b><b>狼人的公司</b><b>-部门</b></p>
                <div>重要提示：本邮件及附件具保密性质，可能包含商业秘密及根据法律享有特权或不得披露的信息。</div>
                <div>如果您意外收到此邮件，特此提醒您本邮件的机密性，请立即回复邮件通知我们并从您的系统中删除本邮件及附件。</div>
                <div>如果您不是本邮件应当的收件人，请注意不可利用、复制本邮件及其附件内容或向他人披露该等内容。</div>
            </div>
            <br>
            <div style="font-size: 11pt;color: #000;line-height: 1.43;"><br>
            </div>
            <div style="font-size: 11pt;color: #000;line-height: 1.43;"><br>
            </div>
        </div>';
        $mail->Body = $mail_content . '<br>' . $mail_sign; // 设置邮件正文
        //$mail->AltBody = strip_tags($body); // 设置邮件正文（纯文本）
        $mail->send(); // 发送邮件
        $result = '邮件发送成功！！'; // 发送成功
    } catch (Exception $e) {
        $result = "邮件发送失败. 原因: {$mail->ErrorInfo}"; // 发送失败
    }
}
