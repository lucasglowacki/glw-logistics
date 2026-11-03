<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>GLW Logistics - Painel Principal</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f3f5f9;
        }

        .painel {
            background: linear-gradient(135deg, #172554, #2563eb);
            border-radius: 15px;
            color: white;
        }

        .card-modulo {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
        }

        .card-modulo:hover {
            transform: translateY(-5px);
        }
    </style>

</head>

<body>

    <!-- MENU SUPERIOR -->

<?php require __DIR__ . "/includes/menu.php"; ?>

    <!-- CONTEÚDO PRINCIPAL -->

    <main class="container py-5">

        <!-- APRESENTAÇÃO -->

        <section class="painel p-4 p-md-5 mb-5">

            <h1 class="fw-bold">
                Painel de Controle
            </h1>

            <p class="lead">
                Bem-vindo ao GLW Logistics.
            </p>

            <p>
                Gerencie produtos, endereços,
                transportadoras, fretes e relatórios
                em um único sistema.
            </p>

            <a href="fretes.php"
               class="btn btn-light fw-bold">

                + Cadastrar Frete

            </a>

        </section>


        <!-- MÓDULOS -->

        <h2 class="mb-4">
            Módulos do Sistema
        </h2>

        <div class="row g-4">

            <!-- PRODUTOS -->

            <div class="col-md-6 col-lg-4">

                <div class="card card-modulo shadow-sm h-100">

                    <div class="card-body p-4">

                        <h3 class="h5">
                            📦 Produtos
                        </h3>

                        <p class="text-muted">
                            Cadastre, consulte, edite
                            e exclua produtos.
                        </p>

                        <a href="produtos.php"
                           class="btn btn-primary">

                            Gerenciar Produtos

                        </a>

                    </div>

                </div>

            </div>


            <!-- ENDEREÇOS -->

            <div class="col-md-6 col-lg-4">

                <div class="card card-modulo shadow-sm h-100">

                    <div class="card-body p-4">

                        <h3 class="h5">
                            📍 Endereços
                        </h3>

                        <p class="text-muted">
                            Gerencie os endereços
                            de origem e destino.
                        </p>

                        <a href="enderecos.php"
                           class="btn btn-primary">

                            Gerenciar Endereços

                        </a>

                    </div>

                </div>

            </div>


            <!-- TRANSPORTADORAS -->

            <div class="col-md-6 col-lg-4">

                <div class="card card-modulo shadow-sm h-100">

                    <div class="card-body p-4">

                        <h3 class="h5">
                            🚚 Transportadoras
                        </h3>

                        <p class="text-muted">
                            Cadastre e gerencie
                            as empresas de transporte.
                        </p>

                        <a href="transportadoras.php"
                           class="btn btn-primary">

                            Gerenciar Transportadoras

                        </a>

                    </div>

                </div>

            </div>


            <!-- FRETES -->

            <div class="col-md-6 col-lg-4">

                <div class="card card-modulo shadow-sm h-100">

                    <div class="card-body p-4">

                        <h3 class="h5">
                            📋 Fretes
                        </h3>

                        <p class="text-muted">
                            Registre fretes com
                            transportadoras, endereços
                            e múltiplos produtos.
                        </p>

                        <a href="fretes.php"
                           class="btn btn-primary">

                            Gerenciar Fretes

                        </a>

                    </div>

                </div>

            </div>


            <!-- RELATÓRIOS -->

            <div class="col-md-6 col-lg-4">

                <div class="card card-modulo shadow-sm h-100">

                    <div class="card-body p-4">

                        <h3 class="h5">
                            📊 Relatórios
                        </h3>

                        <p class="text-muted">
                            Consulte fretes por data
                            e exporte relatórios CSV.
                        </p>

                        <a href="relatorios.php"
                           class="btn btn-primary">

                            Visualizar Relatórios

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </main>


    <!-- RODAPÉ -->

    <footer class="text-center text-muted py-4">

        <small>
            GLW Logistics —
            Projeto Acadêmico de Engenharia de Software
        </small>

    </footer>

</body>

</html>