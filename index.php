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
    if ($media >= MEDIA_APROVACAO) {
        return ["Aprovado", "success"];
    } elseif ($media >= NOTA_RECUPERACAO) {
        return ["Recuperação", "warning"];
    }
    return ["Reprovado", "danger"];
}

function precisaAtencao(array $notas): bool {
    return min($notas) < NOTA_RECUPERACAO;
}

if (!isset($_SESSION['boletim'])) {
    $_SESSION['boletim'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = $_POST['nome'];
    $nota1 = (float) $_POST['nota1'];
    $nota2 = (float) $_POST['nota2'];
    $nota3 = (float) $_POST['nota3'];

    $_SESSION['boletim'][] = [$nome, $nota1, $nota2, $nota3];
}

$boletim = $_SESSION['boletim'];
$totalAlunos = count($boletim);

$numBimestres = $totalAlunos > 0 ? count($boletim[0]) - 1 : 3;

$alunos = [];
$somaTurma = 0;

foreach ($boletim as $item) {
    $nome  = $item[0];
    $notas = array_slice($item, 1);

    $media = calcularMedia($notas);
    [$status, $cor] = getStatus($media);

    $alunos[] = [
        "nome"    => $nome,
        "notas"   => $notas,
        "media"   => $media,
        "status"  => $status,
        "cor"     => $cor,
        "atencao" => precisaAtencao($notas),
    ];

    $somaTurma += $media;
}

if ($totalAlunos > 0) {
    usort($alunos, fn($a, $b) => $b['media'] <=> $a['media']);
    $mediaGeral = $somaTurma / $totalAlunos;
    $melhor = $alunos[0];
    $pior   = end($alunos);
    reset($alunos);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Boletim Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h3 class="text-primary">Cadastro de Aluno</h3>

    <form method="POST" class="mb-4">
        <input type="text" name="nome" class="form-control mb-2" placeholder="Nome do aluno" required>

        <input type="number" step="0.1" min="0" max="10" name="nota1" class="form-control mb-2" placeholder="1º Bimestre" required>
        <input type="number" step="0.1" min="0" max="10" name="nota2" class="form-control mb-2" placeholder="2º Bimestre" required>
        <input type="number" step="0.1" min="0" max="10" name="nota3" class="form-control mb-2" placeholder="3º Bimestre" required>

        <button type="submit" class="btn btn-primary">Adicionar</button>
    </form>
</div>

<?php if ($totalAlunos > 0): ?>

<div class="container">
    <h3 class="text-primary">Boletim da Turma</h3>

    <table class="table table-bordered table-hover mt-3">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Aluno</th>
                <?php for ($i = 1; $i <= $numBimestres; $i++): ?>
                    <th><?= $i ?>º Bim</th>
                <?php endfor; ?>
                <th>Média</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alunos as $index => $aluno): ?>
                <tr>
                    <td><strong><?= $index + 1 ?>º</strong></td>
                    <td><?= e($aluno['nome']) ?></td>

                    <?php foreach ($aluno['notas'] as $nota): ?>
                        <td class="<?= $nota < NOTA_RECUPERACAO ? 'text-danger fw-bold' : '' ?>">
                            <?= number_format($nota, 1, ',', '.') ?>
                        </td>
                    <?php endforeach; ?>

                    <td class="fw-bold"><?= number_format($aluno['media'], 1, ',', '.') ?></td>
                    <td class="text-<?= $aluno['cor'] ?> fw-bold"><?= $aluno['status'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="container mt-4">
    <div class="alert alert-info text-center fs-5">
        Média da Turma: <strong><?= number_format($mediaGeral, 1, ',', '.') ?></strong>
    </div>
</div>

<div class="container mt-4 d-flex gap-3">
    <div class="card border-success flex-fill text-center">
        <div class="card-header bg-success text-white">Melhor Aluno</div>
        <div class="card-body">
            <h4><?= e($melhor['nome']) ?></h4>
            <p>Média: <strong><?= number_format($melhor['media'], 1, ',', '.') ?></strong></p>
        </div>
    </div>

    <div class="card border-danger flex-fill text-center">
        <div class="card-header bg-danger text-white">Pior Desempenho</div>
        <div class="card-body">
            <h4><?= e($pior['nome']) ?></h4>
            <p>Média: <strong><?= number_format($pior['media'], 1, ',', '.') ?></strong></p>
        </div>
    </div>
</div>

<?php endif; ?>

</body>
</html>
