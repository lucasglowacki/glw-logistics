<?php

require_once __DIR__ . "/config/conexao.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("ID inválido.");
}

$sql = "SELECT * FROM produtos WHERE id = :id";

$consulta = $pdo->prepare($sql);

$consulta->execute ([
    ":id" => $id
]);

$produto = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    exit("Produto não encontrado.");
}

$mensagemErro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $nome = trim($_POST["nome"] ??"");
    $descricao = trim($_POST["descricao"] ??"");
    $peso = trim($_POST["peso"] ??"");
    $quantidade = trim($_POST["quantidade"] ??"");

    if (
        $nome === "" ||
        !is_numeric($peso) ||
        (float) $peso <= 0 ||
        filter_var($quantidade, FILTER_VALIDATE_INT) === false ||
        (int) $quantidade < 1
    ) {
        $mensagemErro = "Confira os dados informados.";
    } else {

    $sql = "UPDATE produtos
            SET nome = :nome,
                descricao = :descricao,
                peso = :peso,
                quantidade = :quantidade
            WHERE id = :id";

            $atualizar = $pdo->prepare($sql);

            try {
                $atualizar->execute([
                    ":nome" => $nome,
                    ":descricao" => $descricao,
                    ":peso" => $peso,
                    ":quantidade" => (int) $quantidade,
                    ":id" => $id
                ]);

                header("Location: produtos.php?atualizado=1");
                exit;

            } catch (PDOException $erro) {
                error_log($erro->getMessage());
                $mensagemErro = "Não foi possível atualizar o produto.";
            }
    }

    $produto["nome"] = $nome;
    $produto["descricao"] = $descricao;
    $produto["peso"] = $peso;
    $produto["quantidade"] = $quantidade;   
    }

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar Produto - GLW Logistics</title>

    <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    >
</head> 

<body class="bg-light">

<?php require __DIR__ . "/includes/menu.php"; ?>

    <main class="container mt-5">

    <h1>Editar Produto</h1>

    <?php if ($mensagemErro !== ""): ?>
        <div class="alert alert-danger">
        <?= htmlspecialchars($mensagemErro, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?> 
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input
                type="text"
                name="nome"
                class="form-control"
                value="<?= htmlspecialchars($produto["nome"], ENT_QUOTES, "UTF-8") ?>"
                required
                >
            </div>

            <div class="mb-3">

    <label class="form-label">Descrição</label>

    <textarea
        name="descricao"
        class="form-control"
    ><?= htmlspecialchars($produto["descricao"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>

</div>

            <div class="mb-3">
                <label class="form-label">Peso (kg)</label>

                <input
                    type="number"
                    name="peso"
                    step="0.01"
                    min="0.01"
                    class="form-control"
                    value="<?= htmlspecialchars((string) $produto['peso'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Quantidade</label>

                <input
                    type="number"
                    name="quantidade"
                    min="1"
                    step="1"
                    class="form-control"
                    value="<?= (int) $produto['quantidade'] ?>"
                    required
                >
            </div>

            <button type="submit" class="btn btn-success">
                Salvar Alterações
            </button>

            <a href="produtos.php" class="btn btn-secondary">
                Cancelar
            </a>

        </form>

    </main>

</body>
</html>