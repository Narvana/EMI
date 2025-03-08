<?php

use App\Http\Controllers\EmiInfoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post("/Add/EMI/Info",[EmiInfoController::class,'AddEmi']);
Route::get("/Get/EMI/Info",[EmiInfoController::class,'GetEmi']);
Route::get("/Get/EMI/Installment",[EmiInfoController::class,'GetEMIInstallment']);
Route::put("/Update/Payment/Plan",[EmiInfoController::class, 'UpdatePaymentPlan']);
Route::put("/Update/Installment",[EmiInfoController::class,'UpdatePayment']);