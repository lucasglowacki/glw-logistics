<?php

session_start();

if (empty($_SESSION["csrf_token"])) {

    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

}

require_once __DIR__ . "/config/conexao.php";

try {

    // Buscar as transportadoras cadastradas

    $sqlTransportadoras = "SELECT id, nome
                          FROM transportadoras
                          ORDER BY nome";

    $consultaTransportadoras = $pdo->query($sqlTransportadoras);

    $transportadoras = $consultaTransportadoras->fetchAll(PDO::FETCH_ASSOC);


    // Buscar os endereços cadastrados

    $sqlEnderecos = "SELECT id, identificacao, cidade, uf
                    FROM enderecos
                    ORDER BY identificacao";

    $consultaEnderecos = $pdo->query($sqlEnderecos);

    $enderecos = $consultaEnderecos->fetchAll(PDO::FETCH_ASSOC);


    // Buscar os produtos cadastrados

    $sqlProdutos = "SELECT id, nome
                    FROM produtos
                    ORDER BY nome";

    $consultaProdutos = $pdo->query($sqlProdutos);

    $produtos = $consultaProdutos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $erro) {

    error_log($erro->getMessage());

    http_response_code(500);

    exit("Erro ao carregar os dados do formulário.");

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Fretes - GLW Logistics</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<main class="container mt-5">

    <h1>Cadastro de Fretes</h1>

    <?php if (($_GET["cadastrado"] ?? "") === "1"): ?>

    <div class="alert alert-success">
        Frete cadastrado com sucesso!
    </div>

<?php endif; ?>


<?php if (!empty($_SESSION["erro_frete"])): ?>

    <div class="alert alert-danger">

        <?= htmlspecialchars(
            $_SESSION["erro_frete"],
            ENT_QUOTES,
            "UTF-8"
        ) ?>

    </div>

    <?php unset($_SESSION["erro_frete"]); ?>

<?php endif; ?>

    <p>Preencha as informações para registrar um novo frete.</p>

   <form
    method="POST"
    action="salvar_frete.php"
    class="row g-3"
>

 <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
    >

        <!-- TRANSPORTADORA -->

        <div class="col-md-6">

            <label class="form-label">
                Transportadora
            </label>

            <select name="transportadora_id"
                    class="form-select"
                    required>

                <option value="">
                    Selecione uma transportadora
                </option>

                <?php foreach ($transportadoras as $transportadora): ?>

                    <option value="<?= (int) $transportadora['id'] ?>">

                        <?= htmlspecialchars($transportadora['nome'], ENT_QUOTES, 'UTF-8') ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- DATA DO FRETE -->

        <div class="col-md-6">

            <label class="form-label">
                Data do Frete
            </label>

            <input
                type="date"
                name="data_frete"
                class="form-control"
                required
            >

        </div>


        <!-- ENDEREÇO DE ORIGEM -->

        <div class="col-md-6">

            <label class="form-label">
                Endereço de Origem
            </label>

            <select name="origem_id"
                    class="form-select"
                    required>

                <option value="">
                    Selecione a origem
                </option>

                <?php foreach ($enderecos as $endereco): ?>

                    <option value="<?= (int) $endereco['id'] ?>">

                        <?= htmlspecialchars(
                            $endereco['identificacao'] . " - " .
                            $endereco['cidade'] . "/" .
                            $endereco['uf'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- ENDEREÇO DE DESTINO -->

        <div class="col-md-6">

            <label class="form-label">
                Endereço de Destino
            </label>

            <select name="destino_id"
                    class="form-select"
                    required>

                <option value="">
                    Selecione o destino
                </option>

                <?php foreach ($enderecos as $endereco): ?>

                    <option value="<?= (int) $endereco['id'] ?>">

                        <?= htmlspecialchars(
                            $endereco['identificacao'] . " - " .
                            $endereco['cidade'] . "/" .
                            $endereco['uf'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- PRODUTO -->

     <!-- PRODUTOS DO FRETE -->

<div class="col-12">

    <h4>Produtos do Frete</h4>

    <div id="lista-produtos">

        <div class="row g-3 align-items-end mb-3 produto-linha">

            <!-- PRODUTO -->

            <div class="col-md-7">

                <label class="form-label">
                    Produto
                </label>

                <select
                    name="produto_id[]"
                    class="form-select"
                    required
                >

                    <option value="">
                        Selecione um produto
                    </option>

                    <?php foreach ($produtos as $produto): ?>

                        <option value="<?= (int) $produto['id'] ?>">

                            <?= htmlspecialchars(
                                $produto['nome'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <!-- QUANTIDADE -->

            <div class="col-md-3">

                <label class="form-label">
                    Quantidade
                </label>

                <input
                    type="number"
                    name="quantidade[]"
                    class="form-control"
                    min="1"
                    value="1"
                    required
                >

            </div>

            <!-- REMOVER PRODUTO -->

            <div class="col-md-2">

                <button
                    type="button"
                    class="btn btn-outline-danger w-100 remover-produto"
                >
                    Remover
                </button>

            </div>

        </div>

    </div>

    <!-- ADICIONAR PRODUTO -->

    <button
        type="button"
        id="adicionar-produto"
        class="btn btn-outline-primary"
    >
        + Adicionar Produto
    </button>

</div>

        <!-- OBSERVAÇÕES -->

        <div class="col-12">

            <label class="form-label">
                Observações
            </label>

            <textarea
                name="observacoes"
                class="form-control"
                rows="3"
            ></textarea>

        </div>


        <!-- BOTÕES -->

        <div class="col-12">

            <button
                type="submit"
                class="btn btn-success"
            >
                Salvar Frete
            </button>

            <a
                href="index.php"
                class="btn btn-secondary"
            >
                Voltar ao Painel
            </a>

        </div>

    </form>

</main>

<script>

    const listaProdutos = document.getElementById("lista-produtos");

    const botaoAdicionar = document.getElementById("adicionar-produto");


    // ADICIONAR PRODUTO

    botaoAdicionar.addEventListener("click", function () {

        const primeiraLinha = listaProdutos.querySelector(".produto-linha");

        const novaLinha = primeiraLinha.cloneNode(true);

        novaLinha.querySelector('select[name="produto_id[]"]').value = "";

        novaLinha.querySelector('input[name="quantidade[]"]').value = "1";

        listaProdutos.appendChild(novaLinha);

    });


    // REMOVER PRODUTO

    listaProdutos.addEventListener("click", function (evento) {

        if (!evento.target.classList.contains("remover-produto")) {
            return;
        }

        const linhas = listaProdutos.querySelectorAll(".produto-linha");

        if (linhas.length === 1) {

            alert("O frete precisa ter pelo menos um produto.");

            return;
        }

        const linha = evento.target.closest(".produto-linha");

        linha.remove();

    });

</script>

</body>

</html>