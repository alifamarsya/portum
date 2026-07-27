<?php

namespace App\Providers;

use App\Models\AsMemoSewaCabang;
use App\Models\AsPks;
use App\Models\PgSpk;
use App\Models\UmBiayaHarian;
use App\Models\UmPermintaanCabang;
use App\Policies\ApprovalPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Semua model dengan alur Maker-Checker memakai satu ApprovalPolicy
        // yang sama (CPMK Blockchain: checker != maker).
        foreach ([UmBiayaHarian::class, UmPermintaanCabang::class, AsPks::class, AsMemoSewaCabang::class, PgSpk::class] as $model) {
            Gate::policy($model, ApprovalPolicy::class);
        }
    }
}
