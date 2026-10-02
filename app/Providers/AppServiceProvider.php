<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\PointOfSale;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Policies\AuditLogPolicy;
use App\Policies\BranchPolicy;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\PointOfSalePolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use App\Policies\WarehousePolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Utilizar estilo Bootstrap 5 para paginação
        Paginator::useBootstrapFive();

        // Mapeamento explícito de Policies
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Branch::class, BranchPolicy::class);
        Gate::policy(Warehouse::class, WarehousePolicy::class);
        Gate::policy(PointOfSale::class, PointOfSalePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);

        // Superadmin bypass global
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
            return null;
        });

        // Gate genérico para verificar permissões no contexto da filial ativa
        Gate::define('has-permission', function (User $user, string $permission) {
            return $user->hasPermission($permission, session('active_branch_id'));
        });
    }
}
