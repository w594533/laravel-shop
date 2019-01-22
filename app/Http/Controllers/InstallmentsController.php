<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Installment;

class InstallmentsController extends Controller
{

    public function index()
    {
        $installments = Installment::query()
                            ->where('user_id', \Auth::Id())
                            ->paginate(10);
        return view('installments.index', [
            'installments' => $installments
        ]);                            
    }

    public function show(Installment $installment)
    {
        $this->authorize('own', $installment);
        
        $items = $installment->items()->orderBy('sequence')->get();

        //取出下一次还款
        $nextItem = $items->where('paid_at', null)->first();

        return view('installments.show', [
            'installment' => $installment,
            'items' => $items,
            'nextItem' => $nextItem
        ]);
    }
}
