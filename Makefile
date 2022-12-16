.PHONY : build
build: build-js build-css ## build-js & build-css

.PHONY: build-js
build-js: ## build javascript (concat & minify)
	cat \
	./public/assets/js/main.js > ./public/assets/js/script.concat.js


ifeq (, $(shell which "minifyjs"))
	$(error "minifyjs is not available please install it with: 'npm install -g minifyjs'")
else
	minifyjs -m -i ./public/assets/js/script.concat.js -o ./public/assets/js/script.concat.min.js
endif

.PHONY: build-css
build-css: ## build css (concat)
	cat \
	./public/assets/css/styles.css > ./public/assets/css/styles.concat.css

# Self-Documenting Makefiles utilizing comments starting with
# double hash appended in same line where the rule is defined
# https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help
help: ## prints this help information
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-25s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help