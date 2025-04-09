FROM nginx:latest

COPY hosts/agroplan.conf /etc/nginx/conf.d/agroplan.conf

WORKDIR /data

VOLUME /data

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]