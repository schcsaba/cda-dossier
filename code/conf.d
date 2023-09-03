server {
    server_name xxxxxxxxxxxxxxxxxx.suivideflotte.net;
    listen              80;
    return      301 https://xxxxxxxxxxxxxxxxxx.suivideflotte.net$request_uri;
}
server {
    server_name xxxxxxxxxxxxxxxxxx.suivideflotte.net;
    listen              443 ssl;
    ssl_certificate /etc/nginx/certs/fullchain.pem;
    ssl_certificate_key /etc/nginx/certs/privkey.pem;
    ssl_protocols       TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers         HIGH:!aNULL:!MD5;
    index index.php index.html;
    error_log  /var/log/nginx/app-error.log;
    access_log /var/log/nginx/app-access.log;
    if ($host = 'www.xxxxxxxxxxxxxxxxxx.suivideflotte.net' ) {
       rewrite  ^/(.*)$  https://xxxxxxxxxxxxxxxxxx.suivideflotte.net/$1  permanent;
    }
    root /var/www/apps/pipelinedoc-source/public;
    client_max_body_size 25m;
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass pipelinedoc:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }
}