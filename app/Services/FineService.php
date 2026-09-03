<?php

namespace App\Services;

use App\Models\Loan;
use Carbon\CarbonInterface;

class FineService
{
    public function amount(Loan $loan, ?CarbonInterface $on = null): int
    {
        // Tarif dan masa tenggang harus disahkan sebelum kalkulasi denda diaktifkan.
        return 0;
    }

    public function refresh(Loan $loan, ?CarbonInterface $on = null): Loan
    {
        $amount = $this->amount($loan, $on);
        if ((float) $loan->denda !== $amount) {
            $loan->forceFill(['denda' => $amount])->saveQuietly();
        }

        return $loan;
    }
}
