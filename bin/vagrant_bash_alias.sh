#!/usr/bin/env bash

alias run_lint="cd $HOME/Code && ./vendor/bin/phpcs ./Siteworx --standard=./rules.xml --colors -v"
alias run_lint_fix="cd $HOME/Code && ./vendor/bin/phpcbf ./Siteworx --standard=./rules.xml --colors -v"
alias run_stan="cd $HOME/Code && ./vendor/bin/phpstan analyse --level 0 ./Siteworx/"