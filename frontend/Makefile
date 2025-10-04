help:
	@echo "\033[0;32mmake setup_dev\033[0m - setups everything for local development"
	@echo "\033[0;32mmake set_project_name\033[0m - replaces placeholder slots with project name in files"
	@echo "\033[0;32mmake setup_local_files\033[0m - creates needed files for local development"
	@echo "\033[0;32mmake publish_staging_files\033[0m - creates directory with all the files needed for staging. You still need to set correct environment variables"
	@echo "\033[0;32mmake publish_production_files\033[0m - creates directory with all the files needed for production. You still need to set correct environment variables"

setup_dev: setup_local_files
	@echo "\033[0;32mInstalling packages\033[0m"
	npm install
	@echo "\033[0;32mBuilding images and starting docker containers\033[0m"
	docker compose up -d

FILES := ./docker/local/docker-compose.yml ./docker/staging/docker-compose.yml ./Jenkinsfile ./docker/production/docker-compose.yml
set_project_name:
	@project_name="$(project_name)"; \
	placeholder="{{PROJECT_NAME}}"; \
	if [ -z "$$project_name" ]; then \
		echo "\033[0;31mMissing project_name parameter\033[0m"; \
		exit 1; \
	fi; \
	echo "\033[0;33mSetting project name in:\033[0m"; \
	for file in $(FILES); do \
    	echo "\033[0;32m$$file\033[0m"; \
    	sed -i "s/$$placeholder/$$project_name/g" "$$file"; \
	done

setup_local_files:
	@echo "\033[0;32mMaking a copy of necessary files\033[0m"
	cp docker/local/docker-compose.yml .
	cp .env.example .env

publish_staging_files:
	@mkdir publish/staging -p
	cp docker/staging/docker-compose.yml publish/staging/
	@echo "\033[0;32mStaging files published at publish/staging\033[0m"; \

publish_production_files:
	@mkdir publish/production -p
	cp docker/production/docker-compose.yml publish/production/
	@echo "\033[0;32mStaging files published at publish/production\033[0m"; \
