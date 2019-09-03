SSL certificate installer for Cpanel
===================================

The PHP script takes required parameters and installs a SSL/TLS certificate using Cpanel UAPI's `install_ssl` function. It uses cURL to send payload to the UAPI. It uses Cpanel username and password to authenticate with the API. Connections to the API are made through HTTPS.

An working example can be found [here](https://neurobin.org/docs/web/fully-automated-letsencrypt-integration-with-cpanel/#Download-sslic.php).

#Usage:
The script can be used in CLI environment or by HTTP request. For HTTP request, <span class="warning">do not use GET method</span> (It's insecure), use POST method instead.

##CLI
**Command:**

```sh
php sslic.php domain crt-file key-file CABUNDLE-file/chain-file
```
**Environment Variables:**

    USER:  username
    PASS:  password
    EMAIL: email address
 
##HTTP REQUEST:
**Parameters:**

    user: username
    pass: password
    dom: domain
    crt: Certificate file
    key: Key file
    chain: CABUNDLE file

#Options

Option | Details
------ | -------
domain | Domain name with TLD (e.g: example.com)
cert_file | Path to SSL certificate file
key_file | Path to key file that was used to create CSR
chain_file | Path to CABUNDLE file
`--help`, `-h` | Show help



#Example usage:

```sh
USER='your username' PASS='your password' php sslic.php example.com signed.crt dom.key chain.crt
```
If you want to send email on success or failure, then

```sh
USER='your username' PASS='your password' EMAIL='your email address' php sslic.php example.com signed.crt dom.key chain.crt
```

The shell script **sslic** is a wrapper of the **sslic.php** script. It's provided for convenience of use:

```sh
USER='your username' PASS='your password' ./sslic example.com signed.crt dom.key chain.crt
#Or with email
USER='your username' PASS='your password' EMAIL='your email address' ./sslic example.com signed.crt dom.key chain.crt
```

