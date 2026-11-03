<?php

session_start();

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require_once __DIR__ . "/config/conexao.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!is_int($id) || $id < 1) {

    http_response_code(400);
    exit("ID inválido.");

}

try {

    $sql = "SELECT id, nome, cnpj, telefone, email
            FROM transportadoras
            WHERE id = :id";

    $consulta = $pdo->prepare($sql);

    $consulta->execute([
        ":id" => $id
    ]);

    $transportadora = $consulta->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $erro) {

    error_log($erro->getMessage());

    http_response_code(500);
    exit("Erro ao consultar a transportadora.");

}

if (!$transportadora) {

    http_response_code(404);
    exit("Transportadora não encontrada.");

}

$mensagemErro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $cnpj = trim($_POST["cnpj"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $email = trim($_POST["email"] ?? "");

    $tokenRecebido = $_POST["csrf_token"] ?? "";

    // Mantém os dados digitados caso a validação falhe.

    $transportadora["nome"] = $nome;
    $transportadora["cnpj"] = $cnpj;
    $transportadora["telefone"] = $telefone;
    $transportadora["email"] = $email;

    if (
        !is_string($tokenRecebido) ||
        !hash_equals($_SESSION["csrf_token"], $tokenRecebido)
    ) {

        $mensagemErro = "Sessão inválida. Atualize a página.";

    } elseif ($nome === "" || mb_strlen($nome) > 150) {

        $mensagemErro = "Informe um nome válido.";

    } elseif (
        $cnpj !== "" &&
        !preg_match('/^[0-9]{14}$/', $cnpj)
    ) {

        $mensagemErro = "O CNPJ deve conter 14 números.";

    } elseif (
        mb_strlen($telefone) > 20 ||
        mb_strlen($email) > 150 ||
        ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL))
    ) {

        $mensagemErro = "Verifique o telefone e o e-mail.";

    } else {

        try {

            $sql = "UPDATE transportadoras
                    SET nome = :nome,
                        cnpj = :cnpj,
                        telefone = :telefone,
                        email = :email
                    WHERE id = :id";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                ":nome" => $nome,
                ":cnpj" => $cnpj === "" ? null : $cnpj,
                ":telefone" => $telefone,
                ":email" => $email,
                ":id" => $id
            ]);

            header(
                "Location: transportadoras.php?atualizado=1",
                true,
                303
            );

            exit;

        } catch (PDOException $erro) {

            error_log($erro->getMessage());

            $mensagemErro = "Não foi possível atualizar a transportadora.";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Editar Transportadora - GLW Logistics</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<?php require __DIR__ . "/includes/menu.php"; ?>

    <main class="container mt-5">

        <h1>Editar Transportadora</h1>

        <p>Atualize as informações da transportadora.</p>

        <?php if ($mensagemErro !== ""): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($mensagemErro, ENT_QUOTES, "UTF-8") ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            action="editar_transportadora.php?id=<?= (int) $id ?>"
            class="row g-3"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
            >

            <div class="col-md-6">

                <label class="form-label">
                    Nome da Transportadora
                </label>

                <input
                    type="text"
                    name="nome"
                    maxlength="150"
                    class="form-control"
                    value="<?= htmlspecialchars($transportadora['nome'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <div class="col-md-6">

                <label class="form-label">
                    CNPJ
                </label>

                <input
                    type="text"
                    name="cnpj"
                    maxlength="14"
                    pattern="[0-9]{14}"
                    class="form-control"
                    value="<?= htmlspecialchars($transportadora['cnpj'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >

            </div>

            <div class="col-md-6">

                <label class="form-label">
                    Telefone
                </label>

                <input
                    type="text"
                    name="telefone"
                    maxlength="20"
                    class="form-control"
                    value="<?= htmlspecialchars($transportadora['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >

            </div>

            <div class="col-md-6">

                <label class="form-label">
                    E-mail
                </label>

                <input
                    type="email"
                    name="email"
                    maxlength="150"
                    class="form-control"
                    value="<?= htmlspecialchars($transportadora['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >

            </div>

            <div class="col-12">

                <button
                    type="submit"
                    class="btn btn-success"
                >
                    Salvar Alterações
                </button>

                <a
                    href="transportadoras.php"
                    class="btn btn-secondary"
                >
                    Cancelar
                </a>

            </div>

        </form>

    </main>

</body>

</html>