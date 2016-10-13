SSL certificate installer for Cpanel
===================================

The PHP script takes required parameters and installs a SSL/TLS certificate using Cpanel UAPI's `install_ssl` function.

#Usage:

**sslic.php:**
```sh
php sslic.php domain cert_file key_file chain_file
```
**sslic:**

```sh
./sslic domain cert_file key_file chain_file
```

Option | Details
------ | -------
domain | Domain name with TLD (e.g: example.com)
cert_file | Path to SSL certificate file
key_file | Path to key file that was used to create CSR
chain_file | Path to CABUNDLE file


#Example usage:

```sh
PASS='your password' php sslic.php example.com signed.crt dom.key chain.key
```
If you want to send email on success or failure, then

```sh
PASS='your password' EMAIL='your email address' php sslic.php example.com signed.crt dom.key chain.key
```

The shell script **sslic** is a wrapper of the **sslic.php** script. It's provided for convenience of use:

```sh
PASS='your password' sslic example.com signed.crt dom.key chain.key
#Or with email
PASS='your password' EMAIL='your email address' sslic example.com signed.crt dom.key chain.key
```

