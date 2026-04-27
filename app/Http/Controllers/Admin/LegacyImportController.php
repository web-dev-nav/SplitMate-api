<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Database\Seeders\LegacySqlImportSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class LegacyImportController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $exitCode = Artisan::call('db:seed', [
            '--class' => LegacySqlImportSeeder::class,
            '--force' => true,
        ]);

        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return redirect()
                ->route('admin.dashboard')
                ->with('status', "Legacy import failed. {$output}");
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Legacy import completed.');
    }
}
