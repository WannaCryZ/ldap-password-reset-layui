<?php
include_once './config.php';
// 获取前端提交的邮箱
$email = $_POST['email'] ?? '';
if (empty($email)) {
    die(json_encode(['status' => 'error','message' => '邮箱不能为空']));
}else{
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!$email) {
        die(json_encode(['status' => 'error','message' => '邮箱格式不正确']));
    }
}

// 初始化LDAP连接
$ldapconn = ldap_connect(LDAP_SERVER)
    or die(json_encode(['status' => 'error', 'message' => '无法连接LDAP服务器']));

// 设置协议版本
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

// 绑定管理员账号
$ldapbind = @ldap_bind($ldapconn, LDAP_ADMIN_DN, LDAP_ADMIN_PASSWORD);
if (!$ldapbind) {
    die(json_encode(['status' => 'error', 'message' => 'LDAP绑定失败']));
}

// 构造搜索过滤器
$filter = "(mail=" . ldap_escape($email, "", LDAP_ESCAPE_FILTER) . ")";
$search = ldap_search($ldapconn, LDAP_BASE_DN, $filter);
$info = ldap_get_entries($ldapconn, $search);

header('Content-Type: application/json');
if ($info['count'] > 0) {
    // 检查用户是否存在
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'exists' => true,
        'message' => '已发送重置邮件，请前往企业邮箱查看！',
        'user' => [
            'cn' => $info[0]['cn'][0] ?? '',
            'uid' => $info[0]['uid'][0] ?? ''
        ]
    
    ]);
    $to = $info[0]['mail'][0]; // 收件人邮箱地址
    $givenName = $info[0]['givenname'][0]; // 收件人姓名
    $username = $info[0]['uid'][0]; // 用户名
    $r_send_mail = "";
    // 生成并保存code到数据库
    include_once './common.php';
    $code = generateCode($username); // 生成code
    // 发送重置密码的邮件
    include_once './send_mail.php';
    sendMail($to, $givenName, $username, $code, $r_send_mail);
} else {
    echo json_encode([
        'success' => false,
        'status' => 'warning',
        'exists' => false,
        'message' => '用户不存在'
    ]);
}

ldap_close($ldapconn);

