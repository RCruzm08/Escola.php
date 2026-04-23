<?php
session_start();

const NOTA_RECUPERACAO = 6.0;
const MEDIA_APROVACAO  = 7.0;

function e($valor) {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function calcularMedia(array $notas): float {
    return array_sum($notas) / count($notas);
}

function getStatus(float $media): array {
    if ($media >= MEDIA_APROVACAO) return ["Aprovado","success"];
    if ($media >= NOTA_RECUPERACAO) return ["Recuperação","warning"];
    return ["Reprovado","danger"];
}

if (!isset($_SESSION['boletim'])) {
    $_SESSION['boletim'] = [];
}

if (isset($_GET['remover'])) {
    unset($_SESSION['boletim'][$_GET['remover']]);
    $_SESSION['boletim'] = array_values($_SESSION['boletim']);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['limpar'])) {
    $_SESSION['boletim'] = [];
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['limpar'])) {
    $nome  = $_POST['nome'];
    $nota1 = (float) $_POST['nota1'];
    $nota2 = (float) $_POST['nota2'];
    $nota3 = (float) $_POST['nota3'];

    $notas = [$nota1,$nota2,$nota3];
    $media = calcularMedia($notas);

    if ($_POST['editIndex'] === '') {
        $_SESSION['boletim'][] = [$nome,$nota1,$nota2,$nota3];
    } else {
        $_SESSION['boletim'][$_POST['editIndex']] = [$nome,$nota1,$nota2,$nota3];
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

$boletim = $_SESSION['boletim'];
$editando = null;

if (isset($_GET['editar'])) {
    $editando = $_SESSION['boletim'][$_GET['editar']];
    $editIndex = $_GET['editar'];
} else {
    $editIndex = '';
}

$totalAlunos = count($boletim);

$alunos = [];
$somaTurma = 0;

foreach ($boletim as $item) {
    $nome  = $item[0];
    $notas = array_slice($item,1);

    $media = calcularMedia($notas);
    [$status,$cor] = getStatus($media);

    $alunos[] = [
        "nome"=>$nome,
        "notas"=>$notas,
        "media"=>$media,
        "status"=>$status,
        "cor"=>$cor
    ];

    $somaTurma += $media;
}

if ($totalAlunos > 0) {
    usort($alunos, fn($a,$b)=> $b['media'] <=> $a['media']);
    $mediaGeral = $somaTurma / $totalAlunos;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Boletim</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
<h3 class="text-primary">Cadastro</h3>

<form method="POST">
<input type="hidden" name="editIndex" value="<?= $editIndex ?>">

<input type="text" name="nome" class="form-control mb-2" placeholder="Nome" required value="<?= $editando[0] ?? '' ?>">

<input type="number" step="0.1" min="0" max="10" name="nota1" class="form-control mb-2" placeholder="1º" required value="<?= $editando[1] ?? '' ?>">
<input type="number" step="0.1" min="0" max="10" name="nota2" class="form-control mb-2" placeholder="2º" required value="<?= $editando[2] ?? '' ?>">
<input type="number" step="0.1" min="0" max="10" name="nota3" class="form-control mb-2" placeholder="3º" required value="<?= $editando[3] ?? '' ?>">

<button class="btn btn-primary">Salvar</button>
<button name="limpar" class="btn btn-danger">Limpar</button>
</form>
</div>

<div class="container mt-4">
<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Nome</th>
<th>1º</th>
<th>2º</th>
<th>3º</th>
<th>Média</th>
<th>Status</th>
<th>Ações</th>
</tr>
</thead>
<tbody>

<?php foreach ($boletim as $i => $item): 
$notas = array_slice($item,1);
$media = calcularMedia($notas);
[$status,$cor] = getStatus($media);
?>

<tr>
<td><?= $i+1 ?></td>
<td><?= e($item[0]) ?></td>

<?php foreach ($notas as $n): ?>
<td class="<?= $n < NOTA_RECUPERACAO ? 'text-danger fw-bold':'' ?>">
<?= number_format($n,1,',','.') ?>
</td>
<?php endforeach; ?>

<td><?= number_format($media,1,',','.') ?></td>
<td class="text-<?= $cor ?> fw-bold"><?= $status ?></td>

<td>
<a href="?editar=<?= $i ?>" class="btn btn-sm btn-warning">Editar</a>
<a href="?remover=<?= $i ?>" class="btn btn-sm btn-danger">X</a>
</td>
</tr>

<?php endforeach; ?>

</tbody>
</table>
</div>

<?php if ($totalAlunos > 0): ?>
<div class="container mt-3">
<div class="alert alert-info text-center">
Média da turma: <strong><?= number_format($mediaGeral,1,',','.') ?></strong>
</div>
</div>
<?php endif; ?>

</body>
</html>
