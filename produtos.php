<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require_once __DIR__ . "/config/conexao.php";

$mensagem = "";
$tipoAlerta = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $peso = $_POST["peso"] ?? "";
    $quantidade = $_POST["quantidade"] ?? "";

    if (
        $nome === "" ||
        !is_numeric($peso) ||
        (float)$peso <= 0 ||
        filter_var($quantidade, FILTER_VALIDATE_INT) === false ||
        (int)$quantidade < 1
    ) {

        $mensagem = "Confira os dados informados.";
        $tipoAlerta = "danger";

    } else {

        try {

            $sql = "INSERT INTO produtos
                    (nome, descricao, peso, quantidade)
                    VALUES
                    (:nome, :descricao, :peso, :quantidade)";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                ":nome" => $nome,
                ":descricao" => $descricao,
                ":peso" => $peso,
                ":quantidade" => (int)$quantidade
            ]);

            $mensagem = "Produto cadastrado com sucesso!";

        } catch (PDOException $erro) {

            error_log($erro->getMessage());

            $mensagem = "Erro ao cadastrar produto.";
            $tipoAlerta = "danger";

        }

    }

}

$produtos = [];

try {

$sql = "SELECT id, nome, quantidade FROM produtos ORDER BY id DESC";

$consulta = $pdo->query($sql);

$produtos = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $erro) {

error_log($erro->getMessage());

$mensagem = "Não foi possível carregar os produtos,";
$tipoAlerta = "danger";

}

?>
<!DOCTYPE   html>
<html lang="pt-BR">         
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content"width=device, initial-scale=1.0">
    
    <title>"GLW Logistics"</title>
    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    >
</head> 

<body class="bg-light">

<main class="container mt-5">
    
<h1>Cadastro de Produtos</h1>

<?php if (isset($_GET["excluido"])): ?>

    <div class="alert alert-success">
        Operação de exclusão realizada!
    </div>

<?php elseif (isset($_GET["erro_exclusao"])): ?>

    <div class="alert alert-danger">
        Não foi possível excluir o produto.
    </div>

<?php endif; ?>

<?php if ($mensagem !== ""): ?>

    <div class="alert alert-<?= $tipoAlerta ?>">
        <?= htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8") ?>
    </div>

<?php endif; ?>

<form method="POST">

<div class="mb-3">
    <label for="nome" class="form-label">Nome do Produto</label>
    <input type="text" name="nome" class="form-control" required>
    </div>

    <div class="mb-3">
    <label class="form-label">Descrição</label>
    <textarea name="descricao" class="form-control"></textarea>
</div>

<div class="mb-3">
    <label class="form-label">Peso (kg)</label>
    <input type="number" name="peso"
           step="0.01" min="0.01"
           class="form-control" required>
</div>

<div class="mb-3">
    <label class="form-label">Quantidade</label>
    <input type="number" name="quantidade"
           min="1" step="1"
           class="form-control" required>
</div>

    <button type="submit" class="btn btn-primary">
        Cadastrar Produto
    </button>
</form> 
<h2 class="mt-5">Produtos Cadastrados</h2>

<?php if (empty($produtos)): ?>

    <p>Nenhum produto cadastrado até agora.</p>

    <?php else: ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Opções</th>
                </tr>
            </thead>
<tbody>

    <?php foreach ($produtos as $produto): ?>

        <tr>

            <td><?= (int) $produto["id"] ?></td>

            <td>
                <?= htmlspecialchars($produto["nome"], ENT_QUOTES, "UTF-8") ?>
            </td>

            <td><?= (int) $produto["quantidade"] ?></td>

            <td>
                <a
                    href="editar_produto.php?id=<?= (int) $produto['id'] ?>"
                    class="btn btn-warning btn-sm"
                >
                    Editar
                </a>

<form
    action="excluir_produto.php"
    method="POST"
    class="d-inline"
    onsubmit="return confirm('Tem certeza que deseja excluir este produto?');"
>

    <input
        type="hidden"
        name="id"
        value="<?= (int) $produto['id'] ?>"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
    >

    <button
        type="submit"
        class="btn btn-danger btn-sm"
    >
        Excluir
    </button>

</form>
            </td>    

        </tr>

    <?php endforeach; ?>

</tbody>

        </table>

        <?php endif; ?>