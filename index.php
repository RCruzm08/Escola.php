<?php
session_start();

const NOTA_RECUPERACAO = 5.0;
const MEDIA_APROVACAO  = 7.0;
const MEDIA_OBSERVACAO = 6.0;

function e($valor) {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function calcularMedia(array $notas): float {
    return array_sum($notas) / count($notas);
}

function getStatus(float $media): array {
    if ($media >= MEDIA_APROVACAO)  return ["Aprovado",    "success"];
    if ($media >= MEDIA_OBSERVACAO) return ["Observação",  "warning"];
    if ($media >= NOTA_RECUPERACAO) return ["Recuperação", "secondary"];
    return ["Reprovado", "danger"];
}

function avaliarDestaque(float $media): string {
    if ($media >= 9.0) {
        return " <strong class='text-warning'>(Destaque!)</strong>";
    }
    return "";
}

function verificarRiscoRecuperacao(float $n1, float $n2, float $n3): bool {
    return $n1 < NOTA_RECUPERACAO || $n2 < NOTA_RECUPERACAO || $n3 < NOTA_RECUPERACAO;
}

if (!isset($_SESSION['boletim'])) {
    $_SESSION['boletim'] = [];
}

if (isset($_GET['remover'])) {
    $i = filter_var($_GET['remover'], FILTER_VALIDATE_INT);
    if ($i !== false && isset($_SESSION['boletim'][$i])) {
        unset($_SESSION['boletim'][$i]);
        $_SESSION['boletim'] = array_values($_SESSION['boletim']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['limpar'])) {
    $_SESSION['boletim'] = [];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['limpar'])) {
    $nome  = mb_substr(trim($_POST['nome']), 0, 100);
    $nota1 = max(0, min(10, (float) $_POST['nota1']));
    $nota2 = max(0, min(10, (float) $_POST['nota2']));
    $nota3 = max(0, min(10, (float) $_POST['nota3']));

    $editIndex = filter_var($_POST['editIndex'], FILTER_VALIDATE_INT);

    if ($editIndex === false) {
        $_SESSION['boletim'][] = [$nome, $nota1, $nota2, $nota3];
    } else {
        $_SESSION['boletim'][$editIndex] = [$nome, $nota1, $nota2, $nota3];
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$boletim   = $_SESSION['boletim'];
$editando  = null;
$editIndex = '';

if (isset($_GET['editar'])) {
    $ei = filter_var($_GET['editar'], FILTER_VALIDATE_INT);
    if ($ei !== false && isset($_SESSION['boletim'][$ei])) {
        $editando  = $_SESSION['boletim'][$ei];
        $editIndex = $ei;
    }
}

$totalAlunos  = count($boletim);
$alunos       = [];
$somaTurma    = 0;

foreach ($boletim as $idx => $item) {
    $notas          = array_slice($item, 1);
    $media          = calcularMedia($notas);
    [$status, $cor] = getStatus($media);

    $alunos[] = [
        "idx"    => $idx,
        "nome"   => $item[0],
        "notas"  => $notas,
        "media"  => $media,
        "status" => $status,
        "cor"    => $cor,
    ];

    $somaTurma += $media;
}

$melhorAluno  = null;
$piorAluno    = null;
$emObservacao = [];
$mediaGeral   = null;

if ($totalAlunos > 0) {
    usort($alunos, fn($a, $b) => $b['media'] <=> $a['media']);

    $mediaGeral  = $somaTurma / $totalAlunos;
    $melhorAluno = $alunos[0];
    $piorAluno   = $alunos[$totalAlunos - 1];

    foreach ($alunos as $a) {
        if ($a['media'] >= MEDIA_OBSERVACAO && $a['media'] < MEDIA_APROVACAO) {
            $emObservacao[] = $a;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Boletim Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

    <h3 class="mb-4 fw-bold">Boletim Escolar</h3>

    <div class="card mb-4">
        <div class="card-header fw-semibold">
            <?= $editando ? 'Editar Aluno' : 'Cadastrar Aluno' ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="editIndex" value="<?= $editIndex ?>">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="nome" class="form-control" placeholder="Nome do aluno" required maxlength="100" value="<?= e($editando[0] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.1" min="0" max="10" name="nota1" class="form-control" placeholder="1ª Nota" required value="<?= $editando[1] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.1" min="0" max="10" name="nota2" class="form-control" placeholder="2ª Nota" required value="<?= $editando[2] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.1" min="0" max="10" name="nota3" class="form-control" placeholder="3ª Nota" required value="<?= $editando[3] ?? '' ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Salvar</button>
                        <button type="submit" name="limpar" class="btn btn-outline-danger w-100">Limpar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($totalAlunos > 0): ?>

    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-success h-100">
                <div class="card-header bg-success text-white fw-semibold">Melhor Aluno</div>
                <div class="card-body">
                    <h5 class="card-title fw-bold"><?= e($melhorAluno['nome']) ?></h5>
                    <p class="card-text mb-1">
                        Média: <span class="badge bg-success fs-6"><?= number_format($melhorAluno['media'], 1, ',', '.') ?></span>
                    </p>
                    <span class="badge bg-success"><?= $melhorAluno['status'] ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-danger h-100">
                <div class="card-header bg-danger text-white fw-semibold">Pior Aluno</div>
                <div class="card-body">
                    <h5 class="card-title fw-bold"><?= e($piorAluno['nome']) ?></h5>
                    <p class="card-text mb-1">
                        Média: <span class="badge bg-danger fs-6"><?= number_format($piorAluno['media'], 1, ',', '.') ?></span>
                    </p>
                    <span class="badge bg-<?= $piorAluno['cor'] ?>"><?= $piorAluno['status'] ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-warning h-100">
                <div class="card-header bg-warning fw-semibold">Em Observacao (<?= count($emObservacao) ?>)</div>
                <div class="card-body">
                    <?php if (empty($emObservacao)): ?>
                        <p class="text-muted mb-0">Nenhum aluno em observacao.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($emObservacao as $obs): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <?= e($obs['nome']) ?>
                                <span class="badge bg-warning text-dark"><?= number_format($obs['media'], 1, ',', '.') ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">Turma</div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>1a</th>
                        <th>2a</th>
                        <th>3a</th>
                        <th>Media</th>
                        <th>Status</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $pos => $a): ?>
                    <?php $emRisco = verificarRiscoRecuperacao(...$a['notas']); ?>
                    <tr style="<?= $emRisco ? 'background-color: #fff3cd;' : '' ?>">
                        <td><?= $pos + 1 ?></td>
                        <td class="fw-semibold">
                            <?= e($a['nome']) ?>
                            <?= avaliarDestaque($a['media']) ?>
                        </td>
                        <?php foreach ($a['notas'] as $n): ?>
                        <td class="<?= $n < NOTA_RECUPERACAO ? 'text-danger fw-bold' : '' ?>">
                            <?= number_format($n, 1, ',', '.') ?>
                        </td>
                        <?php endforeach; ?>
                        <td class="fw-bold"><?= number_format($a['media'], 1, ',', '.') ?></td>
                        <td><span class="badge bg-<?= $a['cor'] ?>"><?= $a['status'] ?></span></td>
                        <td>
                            <a href="?editar=<?= $a['idx'] ?>"  class="btn btn-sm btn-warning">Editar</a>
                            <a href="?remover=<?= $a['idx'] ?>" class="btn btn-sm btn-danger">Remover</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="5" class="text-end fw-semibold">Media Geral da Turma:</td>
                        <td colspan="3" class="fw-bold"><?= number_format($mediaGeral, 1, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <?php endif; ?>

</div>

</body>
</html>

<?php
function calcularSinalDaRazao(float $dividendo, float $divisor): string {
    $resultado = $dividendo / $divisor;

    if ($resultado > 0) {
        return "Positivo";
    } else {
        return "Negativo";
    }
}
?>
