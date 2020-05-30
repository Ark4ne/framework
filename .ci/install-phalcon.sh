#!/usr/bin/env bash
#
# This file is part of the Phalcon Framework.
#
# (c) Phalcon Team <team@phalcon.io>
#
# For the full copyright and license information, please view the
# LICENSE.txt file that was distributed with this source code.

shopt -s nullglob

# Install phalcon
git clone -b "$PHALCON_VERSION" --depth 1 -q https://github.com/phalcon/cphalcon

cd cphalcon

zephir build 2>&1 || exit 1

phpenv config-add ../.ci/phalcon.ini || exit 1

cd .. || exit 1
