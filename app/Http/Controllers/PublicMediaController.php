<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    /**
     * Serve public receipt/proof files without relying on /storage symlink.
     */
    public function show(string $path): StreamedResponse|Response
    {
        $normalized = ltrim($path, '/');

        if (!str_starts_with($normalized, 'receipts/') && !str_starts_with($normalized, 'settlement-proofs/')) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($normalized)) {
            abort(404);
        }

        return Storage::disk('public')->response($normalized);
    }
}
