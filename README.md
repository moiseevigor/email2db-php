Email2DB intends to convert the email schema into the relational schema

Installation procedure

Install IMAP extension

```
sudo apt-get install php5-imap
```

Enable IMAP extnsions (creates symlink in `/etc/php5/cli/conf.d/`) 

```
sudo php5enmod imap
```

Check out the conf

```
./composer.phar show -p
```

```
sudo apt-get install php5-dev
```

```
sudo pecl install mailparse
```

```
# echo "extension=mailparse.so" > /etc/php5/mods-available/mailparse.ini
```

```
sudo php5enmod mailparse
```