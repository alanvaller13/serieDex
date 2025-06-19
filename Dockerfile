# Usa a imagem base do PHP com Apache
FROM php:8.2-apache

# Copia todos os arquivos do projeto para o diretório padrão do Apache
COPY . /var/www/html/

# Ativa mod_rewrite do Apache (opcional, útil para URLs amigáveis)
RUN a2enmod rewrite
