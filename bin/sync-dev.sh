#!/bin/bash
# Envia a cópia de trabalho do plugin para os devs, para testar antes do commit.
# Uso: bin/sync-dev.sh [zincomed|novaporto|ambos]   (padrão: ambos)
# Precisa dos aliases `zincomed` e `novaporto` no ~/.ssh/config. Nunca envia para produção.
set -euo pipefail
cd "$(dirname "$0")/.."

enviar() {
	local host=$1 destino=$2
	echo "→ $host:$destino"
	rsync -rlptz --delete --chmod=Du=rwx,Dgo=rx,Fu=rw,Fgo=r \
		--exclude='.git*' --exclude='.github/' --exclude='.superpowers/' --exclude='docs/' --exclude='bin/' --exclude='.gitignore' --exclude='composer.*' --exclude='phpcs.xml' --exclude='phpstan*' --exclude='.distignore' --exclude='.DS_Store' \
		./ "$host:$destino"
}

alvo=${1:-ambos}
case "$alvo" in
	zincomed|ambos) enviar zincomed domains/zincomed.com/public_html/dev/wp-content/plugins/buy-again-woo/ ;;&
	novaporto|ambos) enviar novaporto domains/farmacianovaporto.pt/public_html/dev/wp-content/plugins/buy-again-woo/ ;;
	zincomed) ;;
	*) echo "Uso: $0 [zincomed|novaporto|ambos]" >&2; exit 1 ;;
esac
