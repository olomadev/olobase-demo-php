
## SSL Instalattion

## Enable Apache Mod Ssl

```
sudo a2enmod ssl
```

## CSR File Generation

https://www.thesslstore.com/knowledgebase/ssl-generate/generate-csr-apache-web-server-using-openssl-v-2/

```
Common Name: *.example.com
Organization: My Company Ltd.
Organization Unit: IT
City or Locality: Berlin
State or Province: Brandenburg
Country: DE
Email: me@example.com
A challange password: leave empty
Optional name: leave empty
```

## Willcard SSL

https://serverfault.com/questions/523297/subdomain-with-ssl-on-wildcard-ssl-cert