<?php
include_once  './config.php';
function getCurrentIP() {
    // 获取客户端IP地址
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
function getCurrentTime() {
    // 获取当前时间
    return date('Y-m-d H:i:s');
}
function saveLog($username, $optype, $logContent ,&$result) {
    // 保存日志到数据库
    $conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME); // 连接数据库
    if ($conn->connect_error) {
        die("连接失败: ". $conn->connect_error); // 连接失败，输出错误信息
    }
    $ip = getCurrentIP(); // 获取客户端IP地址
    $time = getCurrentTime(); // 获取当前时间
    $sql = "INSERT INTO reset_pass_log (operator, operation_date, operation_type, operation_content, ip_addr) VALUES ('$username','$time', '$optype', '$logContent','$ip')"; // 插入日志记录到数据库
    if ($conn->query($sql) === TRUE) {
        $result = 1; // 记录成功，输出提示信息
    } else {
        $result = "Error: ". $sql. "<br>". $conn->error; // 记录失败，输出错误信息
    }
}
function tUserInfo($username) {
    // 从LDAP中获取用户信息
    $ldapconn = ldap_connect(LDAP_SERVER) or die("无法连接LDAP服务器"); // 连接LDAP服务器  
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3); // 设置LDAP协议版本
    $ldapbind = @ldap_bind($ldapconn, LDAP_ADMIN_DN, LDAP_ADMIN_PASSWORD) or die("LDAP绑定失败"); // 绑定LDAP管理员账号
    $filter = "(uid=". ldap_escape($username, "", LDAP_ESCAPE_FILTER). ")"; // 构造搜索过滤器
    $search = ldap_search($ldapconn, LDAP_BASE_DN, $filter); // 执行搜索
    $info = ldap_get_entries($ldapconn, $search); // 获取搜索结果
    if ($info['count'] > 0) { // 如果用户存在
        $userInfo = [ // 返回用户信息
            'cn' => $info[0]['cn'][0]?? '', // 姓名
            'uid' => $info[0]['uid'][0]?? '', // 用户名
            'mail' => $info[0]['mail'][0]?? '', // 邮箱
           'telephoneNumber' => $info[0]['telephonenumber'][0]?? '', // 手机号
           'givenName' => $info[0]['givenname'][0]?? '' // 姓
        ];
        return $userInfo; // 返回用户信息
    } else { // 如果用户不存在
        return null; // 返回null
    }
}
function generateCode($username) {
    // 生成并保存code到数据库
    $conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME); // 连接数据库
    if ($conn->connect_error) {
        die("连接失败: ". $conn->connect_error); // 连接失败，输出错误信息
    }
    $code = md5($username. time()); // 生成code
    $start_time = date('Y-m-d H:i:s'); // 创建时间
    $expire_time = date('Y-m-d H:i:s', strtotime('+1 day')); // 过期时间为1天后
    $sql = "INSERT INTO reset_pass_temp (username, code, start_time, expire_time) VALUES ('$username', '$code', '$start_time', '$expire_time')"; // 插入code到数据库
    if ($conn->query($sql) === TRUE) { // 如果插入成功
        return $code; // 返回code
    } else { // 如果插入失败
        return "Error: ". $sql. "<br>". $conn->error; // 返回错误信息
    }
}
function checkCode($username, $code) {
    // 从数据库中检查code是否存在且未过期
    $conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME); // 连接数据库
    if ($conn->connect_error) {
        die("连接失败: ". $conn->connect_error); // 连接失败，输出错误信息
    }
    $current_time = date('Y-m-d H:i:s'); // 获取当前时间
    $sql = "SELECT * FROM reset_pass_temp WHERE username='$username' AND code='$code' AND expire_time>'$current_time'"; // 构造查询语句
    $result = $conn->query($sql); // 执行查询
    if ($result->num_rows > 0) { // 如果查询结果存在
        return true; // 返回true
    } else { // 如果查询结果不存在
        return false; // 返回false
    }
    $conn->close(); // 关闭数据库连接
}
//统一返回方法
function returnJson($status, $success = false, $message, $data = null) {
    header('Content-Type: application/json');
    $result = [
        'status' => $status,
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ];
    echo json_encode($result);
}