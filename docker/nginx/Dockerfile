FROM nginx:alpine

RUN apk add --no-cache wget

COPY default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]