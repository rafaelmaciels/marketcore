<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Exibe os dados cadastrais da empresa do usuário logado.
     */
    public function show(): View
    {
        $user = Auth::user();
        $this->authorize('viewAny', Company::class);

        $company = $user->company ?? Company::first();

        if (!$company) {
            abort(404, 'Nenhuma empresa cadastrada no sistema.');
        }

        $company->load(['branches.warehouses', 'branches.pointsOfSale']);

        return view('company.show', compact('company'));
    }
}
