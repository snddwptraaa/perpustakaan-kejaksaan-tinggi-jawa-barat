<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVisitorHasCheckedIn
{
    public function handle(Request $request, Closure $next): Response
    {
        $checkedInAt = $request->session()->get('visitor_checked_in_at');

        if (! $request->session()->get('visitor_checked_in') || ! $checkedInAt || $checkedInAt !== now()->toDateString()) {
            $request->session()->forget(['visitor_checked_in', 'visitor_id', 'visitor_checked_in_at']);

            return redirect()->route('kunjungan')->with('info', 'Silakan isi data kunjungan terlebih dahulu untuk membuka katalog.');
        }

        return $next($request);
    }
}
