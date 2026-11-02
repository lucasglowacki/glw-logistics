<!DOCTYPE html>
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

<header class="bg-primary text-white p-4">
<div class="container">
<h1>GLW Logistics</h1>
<p>"Sistema de Gerenciamento Logístico"</p>
</div>
</header>

<main class="container mt-4">
    <h2>Painel de Controle</h2>
        <p>Bem vindo ao Sistema</p>

        <?php

        $nomeSistema = "GLW Logistics";

        echo "<p>0 sistema $nomeSistema está funcionando!</p>";

        ?>

        <div class="card mt-4">
            <div class="card-body">
                <h3 class="h5">Modulos do Sistema</h3>

                <ul>

                <li>Produtos</li>
                <li>Endereços</li>
                <li>Transportadoras</li>
                <li>Fretes</li>
                <li>Relatorios</li>
</ul>
</div>
</div>

</main>

</body>
</html>