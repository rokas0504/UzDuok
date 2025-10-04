FROM node:22.12-alpine AS base

ARG API_URL
ENV NUXT_PUBLIC_SANCTUM_BASE_URL=$API_URL
ARG ORIGIN
ENV NUXT_PUBLIC_SANCTUM_ORIGIN=$ORIGIN

WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

FROM node:22.12-alpine AS front
WORKDIR /app
ADD package.json .
ADD nuxt.config.ts .
COPY --from=base /app/.output /app/.output

ENV PORT=80
ENV HOST=0.0.0.0
EXPOSE 80

CMD ["node", ".output/server/index.mjs"]