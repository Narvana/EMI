<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmiInfo;
use App\Models\emi_installment;
use App\Models\fore_Closure;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\returnSelf;

class EmiInfoController extends Controller
{
    

    protected function EmiCalculation(){
    
    }

    public function AddEmi(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'PlotPrincipalAmount' => 'required|numeric', 
                // 'DownPayment' => 'required|numeric',
                'EmiDate' => 'required|date_format:Y-m-d', 
                'LoanTenure' => 'required|integer', 
                'InterestRate' => 'required|numeric'   // annual
                // 'Interest Type' => 'Reducing Balance Method'
            ], [
                // 'EmiDate.required' => 'The EMI date is required.',
                'EmiDate.date_format' => 'The EMI date must be in the format Y-m-d (e.g., 2024-02-28).',
                // 'LoanTenure.required' => 'The loan tenure is required.',
                // 'LoanTenure.integer' => 'The loan tenure must be an integer.',
                // 'InterestRate.required' => 'The interest rate is required.',
                // 'InterestRate.numeric' => 'The interest rate must be a number.'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->first(); 
                return response()->json([
                    'success' => 0,
                    'error' => $errors
                ], 422);
            }
        
            $principal = $request->PlotPrincipalAmount; 

            $loanTenure = $request->LoanTenure; 

            $annualInterestRate = $request->InterestRate;

            // Calculate the EMI using the formula
            $monthlyInterestRate = $annualInterestRate / 100 / 12;
       
            $emi = ($principal * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $loanTenure)) / (pow(1 + $monthlyInterestRate, $loanTenure) - 1);
        
            $totalAmountPaid = $emi * $loanTenure;
            $totalInterestPaid = $totalAmountPaid - $principal;

            $EmiAmount = $principal/$loanTenure;
            $MonthInstallment = round($emi, 2);
            $InterestRateAmount = $MonthInstallment - $EmiAmount;

            //  return response()->json(
            //     [
            //         'PlotPrincipalAmount' => $principal,
            //         'EmiDate' => $request->EmiDate,
            //         'LoanTenure' => $loanTenure,
            //         'InterestRate' => $annualInterestRate,
            //         'monthlyInterestRate'=>$monthlyInterestRate, 
            //         'emi'=>$emi,
            //         'EmiAmount' => round($EmiAmount,2),
            //         'InterestRateAmount' => round($InterestRateAmount,2),
            //         'MonthInstallment' =>  $MonthInstallment,
            //         'TotalInterestAmount' => round($totalInterestPaid, 2),
            //         "TotalAmountPaid"=>$totalAmountPaid,
            //     ]); 
            // Create the EMI record in the database
            $emiRecord =  EmiInfo::create([
                'PlotPrincipalAmount' => $principal,
                'EmiDate' => $request->EmiDate,
                'LoanTenure' => $loanTenure,
                'InterestRate' => $annualInterestRate,
                'EmiAmount' => round($EmiAmount,2),
                'InterestRateAmount' => round($InterestRateAmount,2),
                'MonthInstallment' =>  $MonthInstallment,
                'TotalInterestAmount' => round($totalInterestPaid, 2) // Store the total interest paid
            ]);

            $startDate = Carbon::createFromFormat('Y-m-d', $request->EmiDate); 

            $installments = [];

            for($i=0; $i<$loanTenure;$i++)
            {
                $installmentDate = $startDate->copy()->addMonths($i);

                $installments[] = [
                    'EMI_ID' => $emiRecord->id, // Link to the EMI record
                    'EMI_Amount' => round($MonthInstallment, 2), // Use the calculated monthly installment
                    'EMI_Date' => $installmentDate->format('Y-m-d'), // Format the date as 'Y-m-d'
                    'EMI_Status' => false // Default status, can be changed later
                ];
            }

            emi_installment::insert($installments);

            DB::commit();
        
            return response()->json([
                'success' => 1,
                'data' => $emiRecord,
                'message' => "EMI created with all installments",
            ], 201);
    
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => 0,
                'error' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ], 500);
        }
        
    }

    public function GetEmi(Request $request)
    {
        try {
            //code...
            $Emi = EMIInfo::get();
            if(empty($Emi))
            {
                 return response()->json(
                 [
                     'success'=>0, 
                     'error' => 'No EMI found yet'
                 ], 500);
            }
            return response()->json(
             [
                 'success'=> 1, 
                 'data' => $Emi
             ], 200);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success'=>0, 
                    'error' => $th->getMessage()
                ], 500);
        }
    }

    public function GetEMIInstallment(Request $request)
    {
        try {
            //code...
            $id=$request->query('EMI_ID');
            if(!$id)
            {
                return response()->json(
                    [
                        'success'=>0, 
                        'error' => 'Please provide EMI ID'
                    ], 400); 
            }
            $Emi = emi_installment::where('EMI_ID',$id)->get();
            if(count($Emi) === 0)
            {
                 return response()->json(
                 [
                     'success'=>0, 
                     'error' => 'No EMI installment provided for provided emi installement ID'
                 ], 400);
            }
            return response()->json(
             [
                 'success'=> 1, 
                 'data' => $Emi
             ], 200);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success'=>0, 
                    'error' => $th->getMessage()
                ], 500);
        }
    }

    public function UpdatePaymentPlan(Request $request)
    {

        $validator=Validator::make($request->all(),[
            'EMI_ID' => 'required|numeric|exists:emi_infos,id',
            'PaymentPlanType' => 'required|string|in:FORECLOSURE,FLEXI,MONTHLYEMI',
            'ClosureAmount' => 'required_if:PaymentPlanType,FORECLOSURE|numeric',
            'ChargesPercent' => 'required_if:PaymentPlanType,FORECLOSURE|numeric',
            // 'ClosureCharges' => 'required_if:PaymentPlanType,FORECLOSURE|numeric',
            'ClosureDate' => 'required_if:PaymentPlanType,FORECLOSURE|date',
            'PaymentType' =>'required_if:PaymentPlanType,FORECLOSURE|string',
            'TransactionID' =>'required_if:PaymentPlanType,FORECLOSURE|string',
            'LoanTenure'=> 'required_if:PaymentPlanType,FLEXI|numeric',
            'InterestRate'=> 'required_if:PaymentPlanType,FLEXI|numeric'
            ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first(); 
            return response()->json([
                'success' => 0,
                'error' => $errors
            ], 422);
        }

        try {

            DB::beginTransaction();

            $emiInfo = EmiInfo::where('id',$request->EMI_ID)->first();
            if(!$emiInfo)
            {
                return response()->json([
                    'success' => 0,
                    'message' => 'No EMI info found for provided ID'
                ]);
            }
            // return response()->json('hello');
            if ($request->PaymentPlanType === 'FORECLOSURE') 
            {
                $closureCharges = ($request->ClosureAmount) * ($request->ChargesPercent / 100);
                
                fore_Closure::create([
                    'EMI_ID' => $request->EMI_ID,
                    'ClosureAmount' => $request->ClosureAmount,
                    'ChargesPercent' => $request->ChargesPercent,
                    'ClosureCharges' => $closureCharges,
                    'ClosureDate' => $request->ClosureDate
                ]);
        
                $EMIList = emi_installment::where('EMI_ID', $request->EMI_ID)
                    ->where('EMI_Status', false)
                    ->get();
        
                $PaidEMI = $emiInfo->LoanTenure - count($EMIList);
                if ($EMIList->isNotEmpty()) {
                    $EMIList->each->delete();
                }
        
                emi_installment::create([
                    'EMI_ID' => $request->EMI_ID,
                    'EMI_Amount' => $closureCharges + $request->ClosureAmount,
                    'EMI_Date' => $request->ClosureDate,
                    'EMI_Status' => true,
                    'PaymentType' => $request->PaymentType,
                    'TransactionID' => $request->TransactionID
                ]);

                // Updating emi info table 
                $emiInfo->update([
                    'payment_plan_type' => $request->PaymentPlanType,
                    'LoanTenure' => $PaidEMI,
                    'TotalInterestAmount' => $PaidEMI * $emiInfo->MonthInstallment
                ]);

                // return response()->json($emiInfo);

                DB::commit();

                return response()->json([
                    'success' => 1,
                    'message' => 'Payment Plan Type Updated to ForeClosure',
                    'data' => $emiInfo
                ]);
            }    
            else if($request->PaymentPlanType === 'FLEXI')
            {
                $EMICount = emi_installment::where('EMI_ID', $request->EMI_ID)
                ->where('EMI_Status', true)
                ->count();

                // emiInfo updated
                $paidPrincipal = round($EMICount * $emiInfo->EmiAmount);        

                $newPrincipal = round($emiInfo->PlotPrincipalAmount - $paidPrincipal);  // total loan amount to pay after flexi

                $newTenure= $request->LoanTenure;

                $paidInterestRate= $EMICount * $emiInfo->InterestRateAmount;        

                // return response()->json($paidPrincipal);

                $emiInfo->update([
                    'PlotPrincipalAmount' => $paidPrincipal,
                    'paymentplantype' => $request->PaymentPlanType,
                    'LoanTenure' => $EMICount,
                    'TotalInterestAmount' =>   $paidInterestRate
                ]);
    
                $annualInterestRate = $request->InterestRate;            
             

                $monthlyInterestRate = $annualInterestRate / 100 / 12;
           
                $emi = ($newPrincipal * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $newTenure)) / (pow(1 + $monthlyInterestRate, $newTenure) - 1);
            
                $totalAmountPaid = $emi * $newTenure;
                $totalInterestPaid = $totalAmountPaid - $newPrincipal;
    
                $EmiAmount = $newPrincipal/$newTenure;
                $MonthInstallment = round($emi, 2);
                $InterestRateAmount = $MonthInstallment - $EmiAmount;
    
                $emiDate=$emiInfo->EmiDate;
                // Create the EMIInfo for flexi

                // return response()->json($newPrincipal);

                $emiRecord = EmiInfo::create([
                    'PlotPrincipalAmount' => $newPrincipal,
                    'EmiDate' =>  $emiDate,
                    'LoanTenure' => $newTenure,
                    'InterestRate' => $annualInterestRate,
                    'EmiAmount' => round($EmiAmount,2),
                    'InterestRateAmount' => round($InterestRateAmount,2),
                    'MonthInstallment' =>  $MonthInstallment,
                    'TotalInterestAmount' => round($totalInterestPaid, 2) // Store the total interest paid
                ]);


                emi_installment::where('EMI_ID', $request->EMI_ID)
                ->where('EMI_Status', false)
                ->delete();

                $startDate = Carbon::createFromFormat('Y-m-d',  $emiDate); 
    
                $installments = [];
    
                for($i=0; $i<$newTenure;$i++)
                {
                    $installmentDate = $startDate->copy()->addMonths($i);
    
                    $installments[] = [
                        'EMI_ID' => $request->EMI_ID, // Link to the EMI record
                        'EMI_Amount' => round($MonthInstallment, 2), // Use the calculated monthly installment
                        'EMI_Date' => $installmentDate->format('Y-m-d'), // Format the date as 'Y-m-d'
                        'EMI_Status' => false // Default status, can be changed later
                    ];
                }
    
                emi_installment::insert($installments);


                DB::commit();

                return response()->json([
                    'success' => 1,
                    'data' => $emiRecord,
                    'message' => "EMI added with all installments",
                ], 201);

            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(
                [
                    'success'=>0, 
                    'error' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'trace' => $th->getTraceAsString(),
                ], 500);
        }
    }

    public function UpdatePayment(Request $request)
    {
        // $validator = Validator::make($request->all(),[
        //     'id' => 'required|numeric',
        //     'count' => 'required|numeric',
        //     'TF' => 'required|boolean'
        // ]);
        $id=$request->query('EMI_Installment_ID');
        if(!$id)
        {
            return response()->json(
                [
                    'success'=>0, 
                    'error' => 'Please provide MI_Installment_ID'
                ], 400); 
        }

        $installment = emi_installment::where('id', $id)->first();
         
        if (!$installment) {
            return response()->json(
                [
                    'success'=>0, 
                    'message' => 'No installment found for the given EMI Installment ID.'
                ], 404);
        }
    
            $installment->update([
                'EMI_Status' => !$installment->EMI_Status
            ]);

            if($installment->EMI_Status === true)
            {
                $Status="true";
            }
            else
            {
                $Status="false";
            }

    
        return response()->json([
            'success'=>1, 
            'message' => "Installments updated successfully to {$Status}."
        ], 200);

    }

    // public function removeEMI(Request $request)
    // {

    // }

}


