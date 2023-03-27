<?php
// Conexão com o banco de dados PostgreSQL
$conn = pg_connect("host=localhost dbname=nome_do_banco_de_dados user=nome_de_usuario password=senha");

// Verifica se houve alguma ação no formulário
if (isset($_POST['acao'])) {
    // Cadastrar um produto
    if ($_POST['acao'] == 'cadastrar_produto') {
        $nome = $_POST['nome'];
        $tipo_id = $_POST['tipo_id'];
        $preco = $_POST['preco'];
        $sql = "INSERT INTO produtos (nome, tipo_id, preco) VALUES ('$nome', $tipo_id, $preco)";
        pg_query($conn, $sql);
    }

    // Cadastrar um tipo
    if ($_POST['acao'] == 'cadastrar_tipo') {
        $nome = $_POST['nome'];
        $imposto = $_POST['imposto'];
        $sql = "INSERT INTO tipos (nome, imposto) VALUES ('$nome', $imposto)";
        pg_query($conn, $sql);
    }

    // Realizar uma venda
    if ($_POST['acao'] == 'vender') {
        $produtos = $_POST['produtos'];
        $quantidades = $_POST['quantidades'];
        $total = 0;
        $imposto_total = 0;

        // Loop para calcular o total e o imposto de cada produto
        foreach ($produtos as $key => $produto_id) {
            $quantidade = $quantidades[$key];
            $sql = "SELECT * FROM produtos WHERE id = $produto_id";
            $result = pg_query($conn, $sql);
            $produto = pg_fetch_assoc($result);
            $preco = $produto['preco'];
            $tipo_id = $produto['tipo_id'];
            $sql = "SELECT * FROM tipos WHERE id = $tipo_id";
            $result = pg_query($conn, $sql);
            $tipo = pg_fetch_assoc($result);
            $imposto = $tipo['imposto'];
            $valor = $preco * $quantidade;
            $imposto_valor = $valor * ($imposto / 100);
            $total += $valor;
            $imposto_total += $imposto_valor;
        }

        // Salva a venda no banco de dados
        $sql = "INSERT INTO vendas (produtos, quantidades, total, imposto_total) VALUES ('$produtos', '$quantidades', $total, $imposto_total)";
        pg_query($conn, $sql);

        // Exibe o resultado da venda
        echo "<div class='alert alert-success'>Total da compra: R$ $total</div>";
        echo "<div class='alert alert-success'>Total de impostos: R$ $imposto_total</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sistema de Mercado</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
    <h1>Sistema de Mercado</h1>

    <!-- Formulário para cadastrar um produto -->
    <h2>Cadastrar Produto</h2>
    <form method="POST" action="">
        <input type="hidden" name="acao" value="cadastrar_produto">
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="form-group">
            <label for="tipo_id">Tipo:</label>
            <select class="form-control" id="tipo_id" name="tipo_id" required>
                <?php
                $sql = "SELECT * FROM tipos ORDER BY nome";
                $result = pg_query($conn, $sql);
                while ($row = pg_fetch_assoc($result)) {
                    echo "<option value='".$row['id']."'>".$row['nome']."</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="preco">Preço:</label>
            <input type="number" class="form-control" id="preco" name="preco" step="0.01" required>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
    </form>

    <!-- Formulário para cadastrar um tipo -->
    <h2>Cadastrar Tipo</h2>
    <form method="POST" action="">
        <input type="hidden" name="acao" value="cadastrar_tipo">
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="form-group">
            <label for="imposto">Imposto:</label>
            <input type="number" class="form-control" id="imposto" name="imposto" step="0.01" required>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
    </form>

    <!-- Formulário para realizar uma venda -->
    <h2>Realizar Venda</h2>
    <form method="POST" action="">
        <input type="hidden" name="acao" value="vender">
        <div class="form-group">
            <label for="produtos">Produtos:</label>
            <select class="form-control" id="produtos" name="produtos[]" multiple required>
            <?php
$sql = "SELECT * FROM produtos ORDER BY nome";
$result = pg_query($conn, $sql);
while ($row = pg_fetch_assoc($result)) {
    echo "<option value='".$row['id']."'>".$row['nome']." - R$ ".$row['preco']."</option>";
}
?>
 </select>
        </div>
        <div class="form-group">
            <label for="quantidades">Quantidades:</label>
            <select class="form-control" id="quantidades" name="quantidades[]" multiple required>
                <?php
                for ($i=1; $i<=10; $i++) {
                    echo "<option value='".$i."'>".$i."</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Vender</button>
    </form>

    <!-- Tabela com a lista de produtos -->
    <h2>Lista de Produtos</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Preço</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT p.id, p.nome, t.nome AS tipo, p.preco FROM produtos p
                    JOIN tipos t ON p.tipo_id = t.id ORDER BY p.nome";
            $result = pg_query($conn, $sql);
            while ($row = pg_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".$row['nome']."</td>";
                echo "<td>".$row['tipo']."</td>";
                echo "<td>R$ ".$row['preco']."</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Tabela com a lista de tipos -->
    <h2>Lista de Tipos</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Imposto</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM tipos ORDER BY nome";
            $result = pg_query($conn, $sql);
            while ($row = pg_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".$row['nome']."</td>";
                echo "<td>".$row['imposto']."%</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Tabela com a lista de vendas -->
    <h2>Lista de Vendas</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Valor</th>
                <th>Imposto</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM vendas ORDER BY data DESC";
            $result = pg_query($conn, $sql);
            while ($row = pg_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".date('d/m/Y H:i:s', strtotime($row['data']))."</td>";
                echo "<td>R$ ".$row['valor']."</td>";
                echo "<td>R$ ".$row['imposto']."</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>