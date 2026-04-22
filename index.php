<?php
$boletim = [
    ["Ana Silva",          8.5, 7.0,  9.0],
    ["Klebinho War",       5.0, 6.5,  4.5],
    ["Juninho DJ",         9.0, 9.5, 10.0],
    ["Julia Almeida",      4.0, 5.0,  5.5],
    ["Nathanael da Silva", 7.5, 8.0,  8.5],
];

$somaDaTurma = 0;

$maiorMedia      = 0;
$nomeMelhorAluno = "";
?>

<!-- <div class="container mt-4">
    <h2>Teste de Coordenadas da Matriz</h2>
    <ul>
        <li>O aluno <?php echo $boletim[2][0]; ?> tirou <?php echo $boletim[2][3]; ?> no 3º Bimestre.</li>
        <li>O aluno <?php echo $boletim[1][0]; ?> tirou <?php echo $boletim[1][1]; ?> no 1º Bimestre.</li>
    </ul>
 </div> -->

<div class="container mt-5">
    <h3 class="text-primary">Boletim Geral da Turma</h3>
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>Nome do Aluno</th>
                <th>1º Bim</th>
                <th>2º Bim</th>
                <th>3º Bim</th>
                <th>Média</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            for ($i = 0; $i < count($boletim); $i++) {
                $somaNotas = 0;

                echo "<tr>";
                echo "<td><strong>" . $boletim[$i][0] . "</strong></td>";

                for ($j = 1; $j <= 3; $j++) {
                    echo "<td>" . $boletim[$i][$j] . "</td>";
                    $somaNotas += $boletim[$i][$j];
                }

                $media = $somaNotas / 3;

                echo "<td class='fw-bold text-primary'>" . number_format($media, 1, ',', '.') . "</td>";

                if ($media >= 7.0) {
                    echo "<td class='text-success fw-bold'>Aprovado</td>";
                } else {
                    echo "<td class='text-danger fw-bold'>Reprovado</td>";
                }

                echo "</tr>";

                $somaDaTurma += $media;

                if ($media > $maiorMedia) {
                    $maiorMedia      = $media;
                    $nomeMelhorAluno = $boletim[$i][0];
                }
            }
            ?>
        </tbody>
    </table>
</div>

<?php
$mediaGeral = $somaDaTurma / count($boletim);
?>

<div class="container mt-4">
    <div class="alert alert-info fs-5 text-center">
         Média Geral da Turma: <strong><?php echo number_format($mediaGeral, 1, ',', '.'); ?></strong>
    </div>
</div>

<div class="container mt-4">
    <div class="card border-warning">
        <div class="card-header bg-warning text-dark fw-bold fs-5">
             Pódio — Melhor Aluno da Turma
        </div>
        <div class="card-body text-center">
            <p class="fs-4 mb-1"><strong><?php echo $nomeMelhorAluno; ?></strong></p>
            <p class="text-muted">Média: <span class="fw-bold text-success"><?php echo number_format($maiorMedia, 1, ',', '.'); ?></span></p>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h3 class="text-danger"> Alunos em Atenção</h3>
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-danger">
            <tr>
                <th>Nome do Aluno</th>
                <th>1º Bim</th>
                <th>2º Bim</th>
                <th>3º Bim</th>
            </tr>
        </thead>
        <tbody>
            <?php
            for ($i = 0; $i < count($boletim); $i++) {

                $precisaRecuperacao = false;

                for ($j = 1; $j <= 3; $j++) {
                    if ($boletim[$i][$j] < 6.0) {
                        $precisaRecuperacao = true;
                    }
                }

                if ($precisaRecuperacao) {
                    echo "<tr>";
                    echo "<td><strong>" . $boletim[$i][0] . "</strong></td>";

                    for ($j = 1; $j <= 3; $j++) {
                        $cor = ($boletim[$i][$j] < 6.0) ? " class='text-danger fw-bold'" : "";
                        echo "<td" . $cor . ">" . $boletim[$i][$j] . "</td>";
                    }

                    echo "</tr>";
                }
            }
            ?>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        </tbody>
    </table>
</div>
