FROM node:22.12-slim AS base

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*

# Don't switch to node user - run as root to avoid permission issues with mounted volumes
EXPOSE 3000

# Use shell form to allow for proper signal handling
CMD ["sh", "-c", "npm install && npm run dev"]