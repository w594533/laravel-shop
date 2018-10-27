<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function creating()
    {
        $this->no = Order::findAvailableNo();
    }
}
