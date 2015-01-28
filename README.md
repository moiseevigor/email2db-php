# Email2DB 

Email2DB intends to convert the email schema into the relational schema

## Installation procedure

Install IMAP extension

```
sudo apt-get install php5-imap
```

Enable IMAP extnsions (creates symlink in `/etc/php5/cli/conf.d/`) 

```
sudo php5enmod imap
```

Mailparse installation on Ubuntu 12.04 and later
```
sudo apt-get install php5-dev
```

```
sudo pecl install mailparse
```

```
echo "extension=mailparse.so" > /etc/php5/mods-available/mailparse.ini
```

```
sudo php5enmod mailparse
```

Check out the configuration

```
$ ./composer.phar show -p | grep -E "imap|mail"
ext-imap            0        The imap PHP extension
ext-mailparse       2.1.6    The mailparse PHP extension
```
