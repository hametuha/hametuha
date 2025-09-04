# EC2へのマニュアルインストール

このページを見て確認。

https://dev.classmethod.jp/articles/how-to-install-php82-and-apache-in-ec2-amazon-linux-2023-jp/

```bash
# 成功 remiは不要では？
yum install php8.2 httpd -y

# dnfで検索してみる
dnf search php8.2-*

php8.2-apcu-panel.noarch : APCu control panel
php8.2-bcmath.x86_64 : A module for PHP 8.2 applications for using the bcmath library
php8.2-cli.x86_64 : Command-line interface for PHP 8.2
php8.2-common.x86_64 : Common files for PHP 8.2
php8.2-dba.x86_64 : A database abstraction layer module for PHP 8.2 applications
php8.2-dbg.x86_64 : The interactive PHP 8.2 debugger
php8.2-devel.x86_64 : Files needed for building PHP 8.2 extensions
php8.2-embedded.x86_64 : PHP 8.2 library for embedding in applications
php8.2-enchant.x86_64 : Enchant spelling extension for PHP 8.2 applications
php8.2-ffi.x86_64 : Foreign Function Interface
php8.2-fpm.x86_64 : PHP 8.2 FastCGI Process Manager
php8.2-gd.x86_64 : A module for PHP 8.2 applications for using the gd graphics library
php8.2-gmp.x86_64 : A module for PHP 8.2 applications for using the GNU MP library
php8.2-intl.x86_64 : Internationalization extension for PHP 8.2 applications
php8.2-ldap.x86_64 : A module for PHP 8.2 applications that use LDAP
php8.2-mbstring.x86_64 : A module for PHP 8.2 applications which need multi-byte string handling
php8.2-mysqlnd.x86_64 : A module for PHP 8.2 applications that use MySQL databases
php8.2-odbc.x86_64 : A module for PHP 8.2 applications that use ODBC databases
php8.2-opcache.x86_64 : The Zend OPcache
php8.2-pdo.x86_64 : A database access abstraction module for PHP 8.2 applications
php8.2-pecl-apcu.x86_64 : APC User Cache
php8.2-pecl-apcu-devel.x86_64 : APCu developer files (header)
php8.2-pecl-igbinary.x86_64 : Replacement for the standard PHP serializer
php8.2-pecl-igbinary-devel.x86_64 : Igbinary developer files (header)
php8.2-pecl-msgpack.x86_64 : API for communicating with MessagePack serialization
php8.2-pecl-msgpack-devel.x86_64 : MessagePack developer files (header)
php8.2-pecl-redis6.x86_64 : PHP extension for interfacing with key-value stores
php8.2-pgsql.x86_64 : A PostgreSQL database module for PHP 8.2
php8.2-process.x86_64 : Modules for PHP 8.2 script using system process interfaces
php8.2-pspell.x86_64 : A module for PHP 8.2 applications for using pspell interfaces
php8.2-snmp.x86_64 : A module for PHP 8.2 applications that query SNMP-managed devices
php8.2-soap.x86_64 : A module for PHP 8.2 applications that use the SOAP protocol
php8.2-sodium.x86_64 : Wrapper for the Sodium cryptographic library
php8.2-tidy.x86_64 : Standard PHP 8.2 module provides tidy library support
php8.2-xml.x86_64 : A module for PHP 8.2 applications which use XML
php8.2-zip.x86_64 : ZIP archive management extension
```
