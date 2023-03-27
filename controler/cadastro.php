
<?php
// Cadastro de produtos
if ($_POST["acao"] == "cadastrar_produto") {
    $nome = $_POST["nome"];
    $tipo_id = $_POST["tipo_id"];
    $preco = $_POST["preco"];
    $sql = "INSERT INTO produtos (nome, tipo_id, preco) VALUES ('{$nome}', {$tipo_id}, {$preco})";
    pg_query($conn, $sql);
    header("Location: index.php");
}

// Cadastro de tipos
if ($_POST["acao"] == "cadastrar_tipo") {
    $nome = $_POST["nome"];
    $imposto = $_POST["imposto"];
    $sql = "INSERT INTO tipos (nome, imposto) VALUES ('{$nome}', {$imposto})";
    pg_query($conn, $sql);
    header("Location: index.php");
}

// Tela de venda
if ($_POST["acao"] == "vender") {
    $produtos = $_POST["produtos"];
    $quantidades = $_POST["quantidades"];
    $total = 0;
    $total_impostos = 0;
    for ($i = 0; $i < count($produtos); $i++) {
        $produto_id = $produtos[$i];
        $quantidade = $quantidades[$i];
        $sql = "SELECT produtos.nome, tipos.imposto, produtos.preco FROM produtos JOIN tipos ON produtos.tipo_id = tipos.id WHERE produtos.id = {$produto_id}";
        $result = pg_query($conn, $sql);
        $row = pg_fetch_assoc($result);
        $nome = $row["nome"];
        $imposto = $row["imposto"];
        $preco = $row["preco"];
        $valor = $preco * $quantidade;
        $valor_imposto = $valor * ($imposto / 100);
        $total += $valor;
        $total_impostos += $valor_imposto;
        echo "{$nome}: {$preco} x {$quantidade} = {$valor} (+{$imposto}% imposto = {$valor_imposto})<br>";
    }
    echo "Total: {$total}<br>";
    echo "Total de impostos: {$total_impostos}<br>";
    $sql = "INSERT INTO vendas (produtos, quantidades, total, total_impostos) VALUES ('" . implode(",", $produtos) . "', '" . implode(",", $quantidades) . "', {$total}, {$total_impostos})";
}