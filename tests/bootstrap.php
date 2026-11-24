<?php
/**
 * Utilitários dos testes. Correm no servidor (dev) com `wp eval-file`, contra um carrinho real em memória.
 * Nada aqui é carregado pelo plugin.
 */

if (!defined('ABSPATH')) exit;

$GLOBALS['cl_test_failures'] = 0;
$GLOBALS['cl_cleanups'] = array();

/**
 * Regista uma limpeza. Corre no fim do teste e também quando o teste rebenta com uma
 * exceção ou um erro: o WP-CLI termina o processo antes das register_shutdown_function.
 */
function cl_cleanup($fn) {
    $GLOBALS['cl_cleanups'][] = $fn;
}

function cl_run_cleanups() {
    while ($fn = array_pop($GLOBALS['cl_cleanups'])) {
        try { $fn(); } catch (Throwable $e) { WP_CLI::log('Limpeza falhou: ' . $e->getMessage()); }
    }
}

set_exception_handler(function ($e) {
    WP_CLI::log('FALHOU  erro: ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')');
    cl_run_cleanups();
    WP_CLI::error('o teste parou com um erro');
});
register_shutdown_function('cl_run_cleanups');

function cl_assert($cond, $msg) {
    if ($cond) {
        WP_CLI::log("OK      $msg");
    } else {
        $GLOBALS['cl_test_failures']++;
        WP_CLI::log("FALHOU  $msg");
    }
}

function cl_test_finish() {
    cl_run_cleanups();
    $n = $GLOBALS['cl_test_failures'];
    if ($n > 0) {
        WP_CLI::error("$n teste(s) falharam");
    }
    WP_CLI::success('todos os testes passaram');
}
