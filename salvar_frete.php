<?php

session_start();

require_once __DIR__ . "/config/conexao.php";

// Função utilizada quando encontramos um erro.

function voltarComErro(string $mensagem): void
{
    $_SESSION["erro_frete"] = $mensagem;

    header("Location: fretes.php", true, 303);
    exit;
}

// Aceitar somente requisições POST.

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);
    exit("Método não permitido.");

}

// Validar o token de segurança.

$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token) ||
    !isset($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {

    voltarComErro("Sessão inválida. Tente novamente.");

}

// Recuperar os dados do formulário.

$transportadoraId = filter_input(
    INPUT_POST,
    "transportadora_id",
    FILTER_VALIDATE_INT
);

$origemId = filter_input(
    INPUT_POST,
    "origem_id",
    FILTER_VALIDATE_INT
);

$destinoId = filter_input(
    INPUT_POST,
    "destino_id",
    FILTER_VALIDATE_INT
);

$dataFrete = $_POST["data_frete"] ?? "";

$observacoes = $_POST["observacoes"] ?? "";

$produtos = $_POST["produto_id"] ?? [];

$quantidades = $_POST["quantidade"] ?? [];


// Validar transportadora e endereços.

if (!$transportadoraId || !$origemId || !$destinoId) {

    voltarComErro("Selecione a transportadora e os endereços.");

}

if ($origemId === $destinoId) {

    voltarComErro("Origem e destino precisam ser diferentes.");

}


// Validar a data.

if (
    !is_string($dataFrete) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFrete)
) {

    voltarComErro("Informe uma data válida.");

}

[$ano, $mes, $dia] = explode("-", $dataFrete);

if (!checkdate((int) $mes, (int) $dia, (int) $ano)) {

    voltarComErro("A data informada não existe.");

}


// Validar observações.

if (!is_string($observacoes)) {

    voltarComErro("Observações inválidas.");

}

$observacoes = trim($observacoes);


// Validar os produtos recebidos.

if (
    !is_array($produtos) ||
    !is_array($quantidades) ||
    count($produtos) === 0 ||
    count($produtos) !== count($quantidades)
) {

    voltarComErro("Informe pelo menos um produto válido.");

}


// Organizar os produtos e quantidades.

$itens = [];

foreach ($produtos as $indice => $produto) {

    $produtoId = filter_var(
        $produto,
        FILTER_VALIDATE_INT
    );

    $quantidade = filter_var(
        $quantidades[$indice] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        !$produtoId ||
        !$quantidade ||
        $quantidade < 1
    ) {

        voltarComErro("Verifique os produtos e quantidades.");

    }

    if (isset($itens[$produtoId])) {

        voltarComErro("Não selecione o mesmo produto duas vezes.");

    }

    $itens[$produtoId] = $quantidade;

}


// Salvar os dados no MySQL.

try {

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Iniciar a transação.

    $pdo->beginTransaction();


    // Cadastrar o frete.

    $sql = "INSERT INTO fretes
            (
                transportadora_id,
                origem_id,
                destino_id,
                data_frete,
                observacoes
            )
            VALUES
            (
                :transportadora_id,
                :origem_id,
                :destino_id,
                :data_frete,
                :observacoes
            )";

    $comando = $pdo->prepare($sql);

    $comando->execute([
        ":transportadora_id" => $transportadoraId,
        ":origem_id" => $origemId,
        ":destino_id" => $destinoId,
        ":data_frete" => $dataFrete,
        ":observacoes" => $observacoes
    ]);


    // Recuperar o ID do frete cadastrado.

    $freteId = (int) $pdo->lastInsertId();


    // Preparar o cadastro dos produtos do frete.

    $sqlProdutos = "INSERT INTO frete_produtos
                    (
                        frete_id,
                        produto_id,
                        quantidade
                    )
                    VALUES
                    (
                        :frete_id,
                        :produto_id,
                        :quantidade
                    )";

    $comandoProdutos = $pdo->prepare($sqlProdutos);


    // Cadastrar cada produto vinculado ao frete.

    foreach ($itens as $produtoId => $quantidade) {

        $comandoProdutos->execute([
            ":frete_id" => $freteId,
            ":produto_id" => $produtoId,
            ":quantidade" => $quantidade
        ]);

    }


    // Confirmar todas as operações.

    $pdo->commit();

    header("Location: fretes.php?cadastrado=1", true, 303);
    exit;

} catch (Throwable $erro) {

    // Desfazer tudo caso aconteça algum erro.

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    error_log($erro->getMessage());

    voltarComErro("Não foi possível cadastrar o frete.");

}