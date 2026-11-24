#!/bin/bash
# Envia a cópia de trabalho para os devs e corre os testes PHP (tests/test-*.php).
# Cada teste recebe o nome da loja (novaporto ou zincomed) como primeiro argumento.
# Uso: bin/test-dev.sh [zincomed|novaporto|ambos] [filtro]
set -uo pipefail
cd "$(dirname "$0")/.."

alvo=${1:-ambos}
filtro=${2:-}
bin/sync-dev.sh "$alvo" >/dev/null || exit 1

declare -A WPDIR=([novaporto]=domains/farmacianovaporto.pt/public_html/dev [zincomed]=domains/zincomed.com/public_html/dev)

falhas=0
corridos=0
for loja in novaporto zincomed; do
	[ "$alvo" = ambos ] || [ "$alvo" = "$loja" ] || continue
	for t in tests/test-*${filtro}*.php; do
		[ -e "$t" ] || continue
		echo "== $loja: $t"
		corridos=$((corridos+1))
		saida=$(ssh "$loja" "cd ${WPDIR[$loja]} && wp eval-file wp-content/plugins/buy-again-woo/$t $loja 2>&1" </dev/null)
		echo "$saida" | grep -E "^(OK|FALHOU)|Success:|Error:|Fatal|(Warning|Notice|Deprecated).*buy-again-woo"
		echo "$saida" | grep -q "^Success: todos os testes passaram" || falhas=$((falhas+1))
	done
done
[ $corridos -eq 0 ] && { echo "Nenhum teste corresponde a \"$filtro\""; exit 1; }
[ $falhas -eq 0 ] && echo "TUDO VERDE" || { echo "$falhas ficheiro(s) com falhas"; exit 1; }
