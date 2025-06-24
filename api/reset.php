<?php
include_once 'config.php';
include_once 'common.php';
$username = $_POST['username']; // 从表单中获取用户名
$code = $_POST['code']; // 从表单中获取验证码
$password = $_POST['newpwd']; // 从表单中获取验证码
$confirm_password = $_POST['confirmpwd']; // 从表单中获取二次输入密码

//$email = $_POST['email']; // 从表单中获取邮箱地址
if ($username == null || $code == null || $password == null || $confirm_password == null) {
    returnJson('error',false,'信息校验失败',null);
    exit;
}
//echo "pa:".$password.'<br>'."cpa:".$confirm_password;
if ($password != $confirm_password) { // 验证两次输入的密码是否一致
    returnJson('error',false,'两次输入的密码不一致！',null);
    exit; // 确保脚本终止执行
}

/**
 * SSHA加密算法
 * @param $password  需要加密的字符串
 * @return 返回加密好的字符串
 * */
function ldap_ssha($password)
{
    $salt = "";
    for ($i = 1; $i <= 10; $i++) {
        $salt .= substr('0123456789abcdef', rand(0, 15), 1);
    }
    $hash = "{SSHA}".base64_encode(pack("H*", sha1($password . $salt)) . $salt);
    return $hash;
}


// 连接数据库
$conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
// 查询数据库中是否存在该用户名和验证码
$sql = "SELECT * FROM reset_pass_temp WHERE username = '$username' AND code = '$code' AND expire_time > NOW() AND is_expire = 0"; // 假设表名为 reset_password
$result = $conn->query($sql);
if ($result->num_rows > 0) { // 验证通过，执行密码修改操作
    $ldapconn = ldap_connect(LDAP_SERVER) or die("Could not connect to LDAP server"); // 连接LDAP服务器
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3); // 设置LDAP协议版本为3
    $ldapbind = @ldap_bind($ldapconn, LDAP_ADMIN_DN, LDAP_ADMIN_PASSWORD) or die("LDAP bind failed"); // 绑定LDAP管理员账号
    // 查询用户信息   
    $filter = "(uid=". ldap_escape($username, "", LDAP_ESCAPE_FILTER). ")"; // 构造搜索过滤器
    $search = ldap_search($ldapconn, LDAP_BASE_DN, $filter); // 执行搜索
    $info = ldap_get_entries($ldapconn, $search); // 获取搜索结果
    $user_dn = $info[0]['dn']; // 获取用户的DN
    
    $new_password = ldap_ssha($password); // 调用ldap_ssha函数进行密码加密
    $password_attr = array("userPassword" => $new_password);
    //echo $new_password;
    if (ldap_modify($ldapconn, $user_dn, $password_attr)) { // 修改用户密码
        $sql = "UPDATE reset_pass_temp SET is_expire = 1 WHERE code = '$code'"; // 修改code表中的记录
        $conn->query($sql);
        returnJson('success',true,'密码修改成功！',null);
        //写入日志
        $result = "";
        saveLog($username,"邮件修改密码","修改邮件密码成功",$result);
        //发送通知邮件
        include_once 'send_mail.php';
        sendMailNotice($info[0]['mail'][0],$info[0]['givenname'][0],$username,$result);
        // 关闭数据库连接
        $conn->close(); 
    } else { // 修改用户密码失败
        returnJson('error',false,'密码修改失败！' . ldap_error($ldapconn),null);
        saveLog($username,"邮件修改密码","修改邮件密码失败",$result);
    }
    exit; // 确保脚本终止执行
} else { // 验证失败，提示错误信息
    returnJson('error',false,'用户名或验证码不匹配或验证码已过期，请重新发起重置申请',null);
}


