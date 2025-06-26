# ldap-password-reset-layui
PHP开发的LDAP密码修改工具LAYUI版（LDAP Password Modification Tool for PHP via LAYUI）
### 环境准备
- CentOS 7
- Apache
- PHP 7.0+，启用 LDAP 扩展
- MySQL 5.5+
### 功能设计
#### 1、密码修改
在 PHP 中，可以使用 LDAP 扩展提供的函数来与 LDAP 服务器交互，实现密码修改功能。
> PHP-LDAP扩展函数：https://www.php.net/manual/zh/ref.ldap.php
- `ldap_bind` 函数尝试绑定以验证用户旧密码；
- `ldap_get_entries` 函数查询LDAP用户信息；
- `ldap_modify` 函数操作直接修改 userPassword 属性实现密码修改；
#### 2、邮件发送
- 使用`PHPMailer`函数包。直接用PHP就可以发送，无需搭建额外的Email服务。
#### 3、前端样式
- 使用`LAYUI`样式库
### 技术架构
1、前端使用WEUI样式/Layui样式
2、后端使用PHP语言开发
3、数据库使用MySQL
![Image](https://github.com/user-attachments/assets/a84a882e-8b2c-40eb-bdeb-a0bbe858e6c7)
### 技术方案
#### 用户记得旧密码：
- 使用旧密码验证修改密码，连接到LDAP使用ldap_bind验证用户旧密码是否正确，如果正确，则使用ldap_modify执行修改;
- 修改完成后，将日志写入到MySQL数据库，发送邮件通知用户；
#### 用户不记得旧密码：
- 使用邮箱找回密码，提交邮箱后，后端生成唯一token并存储到数据库，将带有token的链接使用PHPMailer发送邮件给用户；
- 用户点击邮件链接后，先验证code是否有效，无效则跳转到无效提示页面，有效则携带相关用户参数跳转到密码填写页面；
- 用户提交新密码后，连接到MySQL数据库，验证用户名、token；
- 验证通过后，连接LDAP，使用ldap_modify执行密码修改;
- 修改完成后，update数据库，将token标记为无效，将日志写入到MySQL数据库，发送邮件通知用户；
### 界面展示
#### Layui版
![Image](https://github.com/user-attachments/assets/0d5d4d9c-e879-43a3-8c97-a747a91f9342)
#### 邮件界面
![Image](https://github.com/user-attachments/assets/b0b3e151-fa5a-4a90-aa77-ce2cef6ca841)
### 优化方向
1. 前端体验优化
 ●  密码强度实时提示
 ●  验证码倒计时功能
2. 安全增强
 ●  密码修改历史记录
 ●  异常请求检测
3. 扩展能力
 ●  对接企业微信身份验证
 ●  对接企业微信通知
 ●  多LDAP服务器支持
