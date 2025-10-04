help:
	@echo "\033[0;32mmake setup_dev\033[0m - setups everything for local development"
	@echo "\033[0;32mmake create_network\033[0m - creates docker network which will be used for backend and frontend"
	@echo "\033[0;32mmake set_project_name\033[0m - replaces placeholder slots with project name in files"
	@echo "\033[0;32mmake setup_local_files\033[0m - creates needed files for local development"
	@echo "\033[0;32mmake publish_staging_files\033[0m - creates directory with all the files needed for staging. You still need to set correct environment variables"
	@echo "\033[0;32mmake publish_production_files\033[0m - creates directory with all the files needed for production. You still need to set correct environment variables"

setup_dev: setup_local_files
	@echo "\033[0;32mInstalling packages\033[0m"
	composer install
	@echo "\033[0;32mBuilding images and starting docker containers\033[0m"
	docker compose up -d
	@echo "\033[0;32mCreating project key and running migrations with seeders\033[0m"
	./vendor/bin/sail artisan key:generate
	./vendor/bin/sail artisan migrate --seed

FILES := ./.env.testing.example ./.env.example ./docker/local/docker-compose.yml ./docker/production/docker-compose.yml ./docker/staging/docker-compose.yml ./Jenkinsfile
placeholder := {{PROJECT_NAME}}
set_project_name:
	$(CHECK_PROJECT_NAME)
	@echo "\033[0;33mSetting project name in:\033[0m"; \
	for file in $(FILES); do \
    	echo "\033[0;32m$$file\033[0m"; \
    	sed -i "s/$(placeholder)/$(project_name)/g" "$$file"; \
	done

setup_local_files: create_network
	@echo "\033[0;32mMaking a copy of necessary files\033[0m"
	cp docker/local/docker-compose.yml .
	cp .env.example .env
	cp .env.testing.example .env.testing

publish_staging_files:
	@mkdir publish/staging -p
	cp docker/staging/docker-compose.yml publish/staging/
	cp .env.example publish/staging/.env
	@echo "\033[0;32mStaging files published at publish/staging\033[0m"; \

publish_production_files:
	@mkdir publish/production -p
	cp docker/production/docker-compose.yml publish/production/
	cp .env.example publish/production/.env
	@echo "\033[0;32mStaging files published at publish/production\033[0m"; \

create_network:
	$(CHECK_PROJECT_NAME)
	@echo "Creating docker network named: $$project_name-network"
	docker network create -d bridge "$$project_name-network"

define CHECK_PROJECT_NAME
	@project_name="$(project_name)"; \
	if [ -z "$$project_name" ]; then \
		echo "\033[0;31mMissing project_name parameter\033[0m"; \
		exit 1; \
	fi
endef

