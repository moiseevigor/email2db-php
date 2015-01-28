# Email2DB 

Email2DB parses the email schema into the relational schema.

## Installation procedure

We assume the Ubuntu 12.04 and later here on.

### System-wide installation procedure

Install IMAP extension

```
sudo apt-get install php5-imap
```

Enable IMAP extensions (creates symlink in `/etc/php5/cli/conf.d/`) 

```
sudo php5enmod imap
```

Mailparse installation 

```
sudo apt-get install php5-dev
```

Compiling `mailparse`

```
sudo pecl install mailparse
```

And enabling it

```
echo "extension=mailparse.so" > /etc/php5/mods-available/mailparse.ini
```

```
sudo php5enmod mailparse
```

### Project-wide installation procedure

Install composer 

```
curl -sS https://getcomposer.org/installer | php
```

Next install all necessary packages 

```
./composer.phar install
```

No errors should be in the output, if there appear some, please check the previous step. 

Now check out the configuration

```
$ ./composer.phar show -p | grep -E "imap|mail"
ext-imap            0        The imap PHP extension
ext-mailparse       2.1.6    The mailparse PHP extension
```
