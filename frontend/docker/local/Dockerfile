FROM node:22.12-slim AS base

WORKDIR /var/www/html
RUN apt-get update && apt-get install -y curl
USER node
EXPOSE 3000

CMD ["npm", "run", "dev"]