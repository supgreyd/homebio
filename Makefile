# HomeBio Makefile
# Common development tasks

.PHONY: help setup deploy-staging deploy-production

help:
	@echo "HomeBio Development Commands"
	@echo ""
	@echo "  make setup             - Set up local development symlink"
	@echo "  make deploy-staging    - Deploy theme to staging server"
	@echo "  make deploy-production - Deploy theme to production server"
	@echo ""

setup:
	@./scripts/local-setup.sh

deploy-staging:
	@./scripts/deploy.sh staging

deploy-production:
	@./scripts/deploy.sh production
