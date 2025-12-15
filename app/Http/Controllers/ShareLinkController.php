<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FinancialReport;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShareLinkController extends Controller
{
    /**
     * Show the shared reports page.
     */
    public function show(string $token): View|\Illuminate\Http\RedirectResponse
    {
        $shareLink = ShareLink::where('token', $token)->first();

        if (! $shareLink) {
            abort(404, 'Link tidak ditemukan');
        }

        if (! $shareLink->isValid()) {
            if ($shareLink->isExpired()) {
                return view('share.expired', ['shareLink' => $shareLink]);
            }

            abort(404, 'Link tidak aktif');
        }

        // Check if password is required and not yet authenticated
        if ($shareLink->requiresPassword()) {
            $sessionKey = 'share_authenticated_'.$shareLink->id;

            if (! session($sessionKey)) {
                return view('share.password', ['shareLink' => $shareLink, 'token' => $token]);
            }
        }

        // Record the view
        $shareLink->recordView();

        // Get the user's financial reports
        $reports = FinancialReport::where('user_id', $shareLink->user_id)
            ->orderBy('report_date', 'desc')
            ->get();

        // Calculate statistics
        $totalIncome = $reports->where('type', 'income')->sum('amount');
        $totalExpense = $reports->where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        return view('share.view', [
            'shareLink' => $shareLink,
            'reports' => $reports,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
        ]);
    }

    /**
     * Authenticate the share link with password.
     */
    public function authenticate(string $token, Request $request): \Illuminate\Http\RedirectResponse
    {
        $shareLink = ShareLink::where('token', $token)->first();

        if (! $shareLink || ! $shareLink->isValid()) {
            abort(404);
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! $shareLink->checkPassword($request->password)) {
            return back()->withErrors(['password' => 'Password salah']);
        }

        // Store authentication in session
        session(['share_authenticated_'.$shareLink->id => true]);

        return redirect()->route('share.view', $token);
    }
}
