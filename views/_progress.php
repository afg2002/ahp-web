<?php
$currentStep = getCurrentStep();
$currentNum = getStepNumber($currentStep);

$steps = [
    1 => ['page' => 'step1', 'label' => 'Goal', 'sub' => 'Tujuan'],
    2 => ['page' => 'step2', 'label' => 'Kriteria', 'sub' => 'Penilaian'],
    3 => ['page' => 'step3', 'label' => 'Alternatif', 'sub' => 'Pilihan'],
    4 => ['page' => 'step4', 'label' => 'Pairwise', 'sub' => 'Kriteria'],
    5 => ['page' => 'step5', 'label' => 'Perbandingan', 'sub' => 'Alternatif'],
    6 => ['page' => 'results', 'label' => 'Hasil', 'sub' => 'Ranking'],
];

function getStepStatus($stepNum, $currentNum) {
    if ($stepNum < $currentNum) return 'completed';
    if ($stepNum === $currentNum) return 'active';
    return 'inactive';
}
?>
<!-- Progress Steps -->
<div class="mb-10">
    <!-- Desktop -->
    <div class="hidden sm:block">
        <div class="flex items-center justify-between relative">
            <!-- Background line -->
            <div class="absolute top-[18px] left-0 right-0 h-px bg-border -z-0"></div>
            <!-- Active line -->
            <div class="absolute top-[18px] left-0 h-px bg-teal transition-all duration-700 ease-out -z-0"
                 style="width: <?= max(0, ($currentNum - 1) / 5 * 100) ?>%"></div>

            <?php foreach ($steps as $num => $s):
                $status = getStepStatus($num, $currentNum);
                $canClick = $status === 'completed' || $status === 'active';
            ?>
            <div class="flex flex-col items-center relative z-10">
                <?php if ($canClick): ?>
                <a href="?page=<?= $s['page'] ?>"
                   class="step-circle <?= $status ?> <?= $status === 'active' ? 'shadow-sm' : '' ?>">
                <?php else: ?>
                <div class="step-circle <?= $status ?>">
                <?php endif; ?>

                    <?php if ($status === 'completed'): ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    <?php else: ?>
                        <?= $num ?>
                    <?php endif; ?>

                <?php if ($canClick): ?>
                </a>
                <?php else: ?>
                </div>
                <?php endif; ?>

                <span class="step-label <?= $status ?> mt-2 text-center leading-tight">
                    <?= $s['label'] ?>
                    <span class="block text-[10px] uppercase tracking-widest text-ink-light font-normal">
                        <?= $s['sub'] ?>
                    </span>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Mobile -->
    <div class="sm:hidden">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-ink uppercase tracking-wider">
                Langkah <?= $currentNum ?> dari 6
            </span>
            <span class="text-xs text-ink-muted">
                <?= $steps[$currentNum]['label'] ?>
            </span>
        </div>
        <div class="w-full bg-border h-1">
            <div class="progress-bar h-1" style="width: <?= ($currentNum / 6) * 100 ?>%"></div>
        </div>
    </div>
</div>
