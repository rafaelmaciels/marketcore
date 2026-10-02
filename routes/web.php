<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchSwitchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PointOfSaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Rotas de Autenticação (Visitantes)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rotas Protegidas do Sistema ERP (Autenticado e com contexto de Filial Ativa)
Route::middleware(['auth', 'branch.active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Troca de filial ativa
    Route::post('/branch/switch', [BranchSwitchController::class, 'switch'])->name('branch.switch');

    // Dashboard Inicial
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Catálogo de Produtos, Marcas e Categorias
    Route::resource('products', ProductController::class);
    Route::resource('brands', BrandController::class)->except(['create', 'show', 'edit']);
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

    // Empresa & Organização
    Route::get('/company', [CompanyController::class, 'show'])->name('company.show');

    // Filiais
    Route::resource('branches', BranchController::class)->except(['destroy']);

    // Depósitos da Filial Ativa
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');

    // PDVs e Caixas da Filial Ativa
    Route::get('/pos', [PointOfSaleController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PointOfSaleController::class, 'store'])->name('pos.store');
    Route::put('/pos/{pointOfSale}', [PointOfSaleController::class, 'update'])->name('pos.update');

    // Usuários e Acessos
    Route::resource('users', UserController::class);

    // Auditoria e Logs
    Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit/{auditLog}', [AuditLogController::class, 'show'])->name('audit.show');
});
