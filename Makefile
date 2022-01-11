.PHONY : build
build: build-js build-css ## build-js & build-css

.PHONY: build-js
build-js: ## build javascript (concat & minify)
	cat ./public/assets/js/libs/jquery-2.2.4.min.js \
	./public/assets/js/libs/jquery-ui.min.js \
	./public/assets/js/libs/jquery.flexslider-min.js \
	./public/assets/js/libs/jquery.fancybox.js \
	./public/assets/js/libs/jquery.scrollUp.min.js \
	./public/assets/js/libs/jquery.form.js \
	./public/assets/js/libs/jquery.validate.min.js \
	./public/assets/js/libs/makefixed.min.js \
	./public/assets/js/script.js > ./public/assets/js/all.js


ifeq (, $(shell which "minifyjs"))
	$(error "minifyjs is not available please install it with: 'npm install -g minifyjs'")
else
	minifyjs -m -i ./public/assets/js/all.js -o ./public/assets/js/all.min.js
endif

.PHONY: build-css
build-css: ## build css (concat)
	cat ./public/assets/css/normalize.css \
	./public/assets/css/skeleton.css \
	./public/assets/css/flexslider.css \
	./public/assets/css/jquery-ui.css \
	./public/assets/css/jquery.fancybox.css \
	./public/assets/css/jquery.cookiebar.css \
	./public/assets/css/tt_news_v3_styles.css \
	./public/assets/css/style.css > ./public/assets/css/styles-all.css

# Self-Documenting Makefiles utilizing comments starting with
# double hash appended in same line where the rule is defined
# https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help
help: ## prints this help information
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-25s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help