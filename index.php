<?php
const NOTA_RECUPERACAO = 6.0;
const MEDIA_APROVACAO  = 7.0;

$boletim = [
    ["Ana Silva",          8.5, 7.0,  9.0],
    ["Klebinho War",       5.0, 6.5,  4.5],
    ["Juninho DJ",         9.0, 9.5, 10.0],
    ["Julia Almeida",      4.0, 5.0,  5.5],
    ["Nathanael da Silva", 7.5, 8.0,  8.5],
];

$totalAlunos = count($boletim);

if ($totalAlunos === 0) {
    echo "<p class='text-muted'>Nenhum aluno cadastrado.</p>";
    return;
}

$numBimestres = count($boletim[0]) - 1;

$somaTurma       = 0.0;
$maiorMedia      = -1;
$menorMedia      = PHP_FLOAT_MAX;
$nomeMelhorAluno = "";
$nomePiorAluno   = "";

$alunosProcessados = [];

foreach ($boletim as $aluno) {
    $nome  = $aluno[0];
    $notas = array_slice($aluno, 1);
    $media = array_sum($notas) / $numBimestres;

    $somaTurma += $media;

    if ($media > $maiorMedia) {
        $maiorMedia      = $media;
        $nomeMelhorAluno = $nome;
    }

    if ($media < $menorMedia) {
        $menorMedia    = $media;
        $nomePiorAluno = $nome;
    }

    $alunosProcessados[] = [
        "nome"             => $nome,
        "notas"            => $notas,
        "media"            => $media,
        "aprovado"         => $media >= MEDIA_APROVACAO,
        "precisaAtencao"   => in_array(true, array_map(fn($n) => $n < NOTA_RECUPERACAO, $notas)),
    ];
}

$mediaGeral = $somaTurma / $totalAlunos;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletim Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>

<div class="container mt-5">
    <h3 class="text-primary">Boletim Geral da Turma</h3>
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>Nome do Aluno</th>
                <?php for ($b = 1; $b <= $numBimestres; $b++): ?>
                    <th><?php echo $b; ?>º Bim</th>
                <?php endfor; ?>
                <th>Média</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alunosProcessados as $aluno): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($aluno["nome"]); ?></strong></td>
                    <?php foreach ($aluno["notas"] as $nota): ?>
                        <td><?php echo number_format($nota, 1, ',', '.'); ?></td>
                    <?php endforeach; ?>
                    <td class="fw-bold text-primary"><?php echo number_format($aluno["media"], 1, ',', '.'); ?></td>
                    <?php if ($aluno["aprovado"]): ?>
                        <td class="text-success fw-bold">Aprovado</td>
                    <?php else: ?>
                        <td class="text-danger fw-bold">Reprovado</td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="container mt-4">
    <div class="alert alert-info fs-5 text-center">
        Média Geral da Turma: <strong><?php echo number_format($mediaGeral, 1, ',', '.'); ?></strong>
    </div>
</div>

<div class="container mt-4 d-flex gap-3">
    <div class="card border-warning flex-fill">
        <div class="card-header bg-warning text-dark fw-bold fs-5">Melhor Aluno</div>
        <div class="card-body text-center">
            <p class="fs-4 mb-1"><strong><?php echo htmlspecialchars($nomeMelhorAluno); ?></strong></p>
            <p class="text-muted">Média: <span class="fw-bold text-success"><?php echo number_format($maiorMedia, 1, ',', '.'); ?></span></p>
        </div>
    </div>
    <div class="card border-danger flex-fill">
        <div class="card-header bg-danger text-white fw-bold fs-5">Pior Aluno</div>
        <div class="card-body text-center">
            <p class="fs-4 mb-1"><strong><?php echo htmlspecialchars($nomePiorAluno); ?></strong></p>
            <p class="text-muted">Média: <span class="fw-bold text-danger"><?php echo number_format($menorMedia, 1, ',', '.'); ?></span></p>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h3 class="text-danger">Alunos em Atenção</h3>
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-danger">
            <tr>
                <th>Nome do Aluno</th>
                <?php for ($b = 1; $b <= $numBimestres; $b++): ?>
                    <th><?php echo $b; ?>º Bim</th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alunosProcessados as $aluno): ?>
                <?php if ($aluno["precisaAtencao"]): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($aluno["nome"]); ?></strong></td>
                        <?php foreach ($aluno["notas"] as $nota): ?>
                            <?php $cor = $nota < NOTA_RECUPERACAO ? " class='text-danger fw-bold'" : ""; ?>
                            <td<?php echo $cor; ?>><?php echo number_format($nota, 1, ',', '.'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
